<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Plugin\Block\Product;

use Magento\Catalog\Block\Product\Image;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\View\LayoutInterface;
use Venbhas\ProductLabels\Block\Product\Labels;
use Venbhas\ProductLabels\Model\Config;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Injects product labels into all catalog product image output.
 */
class ImagePlugin
{
    /** @var Config */
    private $config;

    /** @var LayoutInterface */
    private $layout;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @param Config $config
     * @param LayoutInterface $layout
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Config $config,
        LayoutInterface $layout,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->layout = $layout;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * Wrap product image HTML with label overlay markup.
     *
     * @param Image $subject
     * @param string $result
     * @return string
     */
    public function afterToHtml(Image $subject, string $result): string
    {
        $storeId = (int) $this->storeManager->getStore()->getId();
        if ($result === '' || !$this->config->isEnabled($storeId)) {
            return $result;
        }

        $product = $this->resolveProduct($subject);
        if (!$product || !$product->getId()) {
            return $this->withDebugComment($result, 'no-product');
        }

        if (!(int) $product->getStoreId()) {
            $product->setStoreId($storeId);
        }

        /** @var Labels $labelsBlock */
        $labelsBlock = $this->layout->createBlock(Labels::class);
        $labelsBlock->setProduct($product);
        $labels = $labelsBlock->getLabels();
        if ($labels === []) {
            return $this->withDebugComment($result, 'no-labels');
        }

        $labelsHtml = $labelsBlock->toHtml();
        if ($labelsHtml === '') {
            return $this->withDebugComment($result, 'empty-template');
        }

        return '<div class="vp-label-wrapper" style="position:relative;display:inline-block;">'
            . $result
            . $labelsHtml
            . '</div>';
    }

    /**
     * Append an HTML comment when developer logging is enabled.
     *
     * @param string $html
     * @param string $reason
     * @return string
     */
    private function withDebugComment(string $html, string $reason): string
    {
        if (!$this->config->isLoggingEnabled((int) $this->storeManager->getStore()->getId())) {
            return $html;
        }

        return $html . '<!-- venbhas-product-labels:' . $reason . ' -->';
    }

    /**
     * Resolve product from image block data.
     *
     * @param Image $subject
     * @return \Magento\Catalog\Model\Product|null
     */
    private function resolveProduct(Image $subject): ?\Magento\Catalog\Model\Product
    {
        $product = $subject->getData('product');
        $productId = 0;

        if ($product instanceof \Magento\Catalog\Model\Product && $product->getId()) {
            $productId = (int) $product->getId();
        } else {
            $productId = (int) $subject->getData('product_id');
        }

        if ($productId <= 0) {
            return null;
        }

        try {
            return $this->productRepository->getById(
                $productId,
                false,
                (int) $this->storeManager->getStore()->getId()
            );
        } catch (\Exception $e) {
            return $product instanceof \Magento\Catalog\Model\Product ? $product : null;
        }
    }
}
