<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Api;

/**
 * Resolved product labels lookup by SKU.
 */
interface ProductLabelManagementInterface
{
    /**
     * Get resolved product labels by SKU.
     *
     * @param string $sku
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBySku(string $sku): array;
}
