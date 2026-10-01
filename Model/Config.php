<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Product Labels module configuration.
 */
class Config
{
    private const XML_PATH_ENABLED = 'catalog/venbhas_product_labels/enabled';
    private const XML_PATH_MAX_LABELS = 'catalog/venbhas_product_labels/max_labels';
    private const XML_PATH_Z_INDEX = 'catalog/venbhas_product_labels/z_index';
    private const XML_PATH_ENABLE_LOGGING = 'catalog/venbhas_product_labels/enable_logging';
    private const XML_PATH_LOG_LEVEL = 'catalog/venbhas_product_labels/log_level';

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check whether the module is enabled.
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get the maximum number of labels per product.
     *
     * @param int|null $storeId
     * @return int
     */
    public function getMaxLabelsPerProduct(?int $storeId = null): int
    {
        $value = (int) $this->scopeConfig->getValue(
            self::XML_PATH_MAX_LABELS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $value > 0 ? $value : 2;
    }

    /**
     * Get the CSS z-index for product labels.
     *
     * @param int|null $storeId
     * @return int
     */
    public function getZIndex(?int $storeId = null): int
    {
        $value = (int) $this->scopeConfig->getValue(
            self::XML_PATH_Z_INDEX,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $value > 0 ? $value : 10;
    }

    /**
     * Check whether debug logging is enabled.
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isLoggingEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLE_LOGGING,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get the configured log level.
     *
     * @param int|null $storeId
     * @return string
     */
    public function getLogLevel(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_LOG_LEVEL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ?: 'info';
    }
    
    /**
     * Whether the module is enabled at default (global) scope — used for admin UI visibility.
     *
     * @return bool
     */
    public function isModuleEnabledForAdmin(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
    }
}
