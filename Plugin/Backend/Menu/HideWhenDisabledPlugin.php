<?php

declare(strict_types=1);

namespace Venbhas\ProductLabels\Plugin\Backend\Menu;

use Magento\Backend\Model\Menu;
use Magento\Backend\Model\Menu\Builder;
use Venbhas\ProductLabels\Model\Config\ModuleEnabledGuard;

/**
 * Removes ProductLabels admin menu entries when the module is disabled at default scope.
 */
class HideWhenDisabledPlugin
{
    /**
     * Menu item IDs registered by Venbhas_ProductLabels.
     */
    private const PRODUCTLABELS_MENU_IDS = [
        'Venbhas_ProductLabels::product_labels_manage',
        'Venbhas_ProductLabels::venbhas',
    ];

    /**
     * @var ModuleEnabledGuard
     */
    private ModuleEnabledGuard $moduleEnabledGuard;

    /**
     * @param ModuleEnabledGuard $moduleEnabledGuard Module enabled guard
     */
    public function __construct(ModuleEnabledGuard $moduleEnabledGuard)
    {
        $this->moduleEnabledGuard = $moduleEnabledGuard;
    }

    /**
     * Strip ProductLabels menu items when the extension is disabled globally.
     *
     * @param Builder $subject Menu builder
     * @param Menu $menu Built menu
     *
     * @return Menu
     */
    public function afterGetResult(Builder $subject, Menu $menu): Menu
    {
        if ($this->moduleEnabledGuard->isEnabledForAdmin()) {
            
            return $menu;
        }

        foreach (self::PRODUCTLABELS_MENU_IDS as $menuId) {
            $menu->remove($menuId);
        }

        return $menu;
    }
}
