<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Block\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Venbhas\ProductLabels\Model\Config;
use Venbhas\ProductLabels\Model\Label\Resolver;

/**
 * Product labels block used by the image plugin.
 */
class Labels extends Template implements IdentityInterface
{
    /** @var string */
    protected $_template = 'Venbhas_ProductLabels::product/labels.phtml';

    /** @var Resolver */
    private $labelResolver;

    /** @var Config */
    protected $config;

    /** @var Registry */
    private $registry;

    /** @var Product|null */
    protected $product;

    /** @var array<int, array<string, mixed>>|null */
    protected $labels;

    /**
     * @param Template\Context $context
     * @param Resolver $labelResolver
     * @param Config $config
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Resolver $labelResolver,
        Config $config,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->labelResolver = $labelResolver;
        $this->config = $config;
        $this->registry = $registry;
    }

    /**
     * Set the product used to resolve labels.
     *
     * @param Product $product
     * @return $this
     */
    public function setProduct(Product $product): self
    {
        $this->product = $product;
        $this->labels = null;
        return $this;
    }

    /**
     * Get the product used to resolve labels.
     *
     * @return Product|null
     */
    public function getProduct(): ?Product
    {
        if ($this->product !== null) {
            return $this->product;
        }

        $product = $this->registry->registry('current_product');
        if ($product instanceof Product) {
            $this->product = $product;
        }

        return $this->product;
    }

    /**
     * Get resolved labels for the current product.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLabels(): array
    {
        if ($this->labels !== null) {
            return $this->labels;
        }

        $product = $this->getProduct();
        if (!$product) {
            $this->labels = [];
            return $this->labels;
        }

        if (!$this->config->isEnabled($this->getStoreId())) {
            $this->labels = [];
            return $this->labels;
        }

        $this->labels = $this->labelResolver->getLabelsForProduct($product);
        return $this->labels;
    }

    /**
     * Resolve the current store ID for label rendering.
     *
     * @return int
     */
    private function getStoreId(): int
    {
        if ($this->product && (int) $this->product->getStoreId() > 0) {
            return (int) $this->product->getStoreId();
        }

        return (int) $this->_storeManager->getStore()->getId();
    }

    /**
     * Get the configured CSS z-index for labels.
     *
     * @return int
     */
    public function getZIndex(): int
    {
        $storeId = $this->product ? (int) $this->product->getStoreId() : null;
        return $this->config->getZIndex($storeId);
    }

    /**
     * @inheritdoc
     */
    public function getIdentities(): array
    {
        return ['venbhas_product_labels'];
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml(): string
    {
        if (!$this->getProduct() || $this->getLabels() === []) {
            return '';
        }

        return parent::_toHtml();
    }
}
