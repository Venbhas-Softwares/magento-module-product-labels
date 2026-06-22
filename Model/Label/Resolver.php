<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Model\Label;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Venbhas\ProductLabels\Api\Data\LabelInterface;
use Venbhas\ProductLabels\Logger\Logger;
use Venbhas\ProductLabels\Model\Config;
use Venbhas\ProductLabels\Model\Label\IdListFormatter;
use Venbhas\ProductLabels\Model\ResourceModel\Label\CollectionFactory;

/**
 * Resolves applicable labels for a product.
 */
class Resolver
{
    private const CACHE_PREFIX = 'venbhas_product_labels_';
    private const CACHE_LIFETIME = 86400;

    /** @var CollectionFactory */
    private $collectionFactory;

    /** @var Config */
    private $config;

    /** @var CustomerSession */
    private $customerSession;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var DiscountCalculator */
    private $discountCalculator;

    /** @var CacheInterface */
    private $cache;

    /** @var SerializerInterface */
    private $serializer;

    /** @var Logger */
    private $logger;

    /** @var IdListFormatter */
    private $idListFormatter;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var HttpContext */
    private $httpContext;

    /** @var ProductValidator */
    private $productValidator;

    /** @var array<string, Product> */
    private $evaluationProductCache = [];

    /**
     * @param CollectionFactory $collectionFactory
     * @param Config $config
     * @param CustomerSession $customerSession
     * @param StoreManagerInterface $storeManager
     * @param DiscountCalculator $discountCalculator
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @param Logger $logger
     * @param IdListFormatter $idListFormatter
     * @param ProductRepositoryInterface $productRepository
     * @param HttpContext $httpContext
     * @param ProductValidator $productValidator
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Config $config,
        CustomerSession $customerSession,
        StoreManagerInterface $storeManager,
        DiscountCalculator $discountCalculator,
        CacheInterface $cache,
        SerializerInterface $serializer,
        Logger $logger,
        IdListFormatter $idListFormatter,
        ProductRepositoryInterface $productRepository,
        HttpContext $httpContext,
        ProductValidator $productValidator
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->config = $config;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->discountCalculator = $discountCalculator;
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->logger = $logger;
        $this->idListFormatter = $idListFormatter;
        $this->productRepository = $productRepository;
        $this->httpContext = $httpContext;
        $this->productValidator = $productValidator;
    }

    /**
     * Resolve applicable labels for a product.
     *
     * @param Product $product
     * @return array<int, array<string, mixed>>
     */
    public function getLabelsForProduct(Product $product): array
    {
        if (!$this->config->isEnabled($this->getCurrentStoreId($product))) {
            return [];
        }

        $storeId = $this->getCurrentStoreId($product);
        $groupId = $this->getCustomerGroupId();
        $productId = (int) $product->getId();
        $cacheKey = self::CACHE_PREFIX . $productId . '_' . $storeId . '_' . $groupId;

        $cached = $this->cache->load($cacheKey);
        if ($cached !== false) {
            $decoded = $this->serializer->unserialize($cached);
            return is_array($decoded) ? $decoded : [];
        }

        $labels = $this->resolveLabels($product, $storeId, $groupId);
        if ($labels !== []) {
            $this->cache->save(
                $this->serializer->serialize($labels),
                $cacheKey,
                ['venbhas_product_labels'],
                self::CACHE_LIFETIME
            );
        }

        if ($this->config->isLoggingEnabled($storeId)) {
            $this->logger->info('Resolved product labels', [
                'product_id' => $productId,
                'store_id' => $storeId,
                'customer_group_id' => $groupId,
                'count' => count($labels),
            ]);
        }

        return $labels;
    }

    /**
     * Match active labels against a product for the given scope.
     *
     * @param Product $product
     * @param int $storeId
     * @param int $groupId
     * @return array<int, array<string, mixed>>
     */
    private function resolveLabels(Product $product, int $storeId, int $groupId): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addActiveFilter($storeId);
        $evaluationProduct = $this->getEvaluationProduct($product, $storeId);

        $matched = [];
        $maxLabels = $this->config->getMaxLabelsPerProduct($storeId);

        foreach ($collection as $label) {
            if (!$this->isCustomerGroupAllowed($label, $groupId)) {
                continue;
            }

            if (!$this->productValidator->validate($label, $evaluationProduct, $storeId)) {
                continue;
            }

            $displayText = $this->getDisplayText($evaluationProduct, $label);
            if ($displayText === null) {
                continue;
            }

            $matched[] = [
                'label_id' => (int) $label->getId(),
                'label_type' => (string) $label->getLabelType(),
                'display_text' => $displayText,
                'bg_color' => (string) $label->getBgColor(),
                'text_color' => (string) $label->getTextColor(),
                'shape' => (string) $label->getShape(),
                'font_size' => (int) $label->getFontSize(),
                'position' => (string) $label->getPosition(),
                'position_x' => (int) $label->getPositionX(),
                'position_y' => (int) $label->getPositionY(),
            ];

            if (count($matched) >= $maxLabels) {
                break;
            }
        }

        return $matched;
    }

    /**
     * Check whether the label applies to the customer group.
     *
     * @param \Venbhas\ProductLabels\Model\Label $label
     * @param int $groupId
     * @return bool
     */
    private function isCustomerGroupAllowed($label, int $groupId): bool
    {
        $groupIds = (string) $label->getCustomerGroupIds();
        if ($groupIds === '') {
            return true;
        }

        $allowed = $this->idListFormatter->explodeIds($groupIds);
        return in_array((string) $groupId, $allowed, true);
    }

    /**
     * Build the display text for a matched label.
     *
     * @param Product $product
     * @param \Venbhas\ProductLabels\Model\Label $label
     * @return string|null
     */
    private function getDisplayText(Product $product, $label): ?string
    {
        if ($label->getLabelType() === LabelInterface::TYPE_DISCOUNT) {
            return $this->discountCalculator->calculate($product, $label);
        }

        $text = trim((string) $label->getTextContent());
        return $text !== '' ? $text : null;
    }

    /**
     * Resolve the current store ID for a product.
     *
     * @param Product $product
     * @return int
     */
    private function getCurrentStoreId(Product $product): int
    {
        $storeId = (int) $product->getStoreId();
        if ($storeId > 0) {
            return $storeId;
        }

        return (int) $this->storeManager->getStore()->getId();
    }

    /**
     * Resolve the customer group for label matching.
     *
     * @return int
     */
    private function getCustomerGroupId(): int
    {
        $groupId = $this->httpContext->getValue(CustomerContext::CONTEXT_GROUP);
        if ($groupId !== null && $groupId !== '') {
            return (int) $groupId;
        }

        return (int) $this->customerSession->getCustomerGroupId();
    }

    /**
     * Load a full product model for rule validation and pricing.
     *
     * @param Product $product
     * @param int $storeId
     * @return Product
     */
    private function getEvaluationProduct(Product $product, int $storeId): Product
    {
        $productId = (int) $product->getId();
        if ($productId <= 0) {
            return $product;
        }

        $cacheKey = $productId . '_' . $storeId;
        if (isset($this->evaluationProductCache[$cacheKey])) {
            return $this->evaluationProductCache[$cacheKey];
        }

        try {
            $loadedProduct = $this->productRepository->getById($productId, false, $storeId);
        } catch (\Exception $e) {
            $loadedProduct = $product;
        }

        $this->evaluationProductCache[$cacheKey] = $loadedProduct;

        return $loadedProduct;
    }
}
