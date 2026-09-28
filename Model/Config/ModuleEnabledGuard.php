<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Model\Config;

use Venbhas\ProductLabels\Model\Config;

/**
 * Central guard for store-config module enable flag (venbhas_ProductLabels/general/enabled).
 */
class ModuleEnabledGuard
{
    /** @var Config */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Whether ProductLabels frontend features should run for the given store.
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled(?int $storeId = null): bool
    {
        return $this->config->isModuleEnabled($storeId);
    }

    /**
     * Whether admin ProductLabels menus and controllers should be available (default config scope).
     *
     * @return bool
     */
    public function isEnabledForAdmin(): bool
    {
        return $this->config->isModuleEnabledForAdmin();
    }
}
