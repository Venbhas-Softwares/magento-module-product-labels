<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Venbhas\ProductLabels\Api\ProductLabelManagementInterface;
use Venbhas\ProductLabels\Model\Label\Resolver;
use Venbhas\ProductLabels\Model\Resolver\DataProvider\LabelData;

/**
 * REST product labels lookup by SKU.
 */
class ProductLabelManagement implements ProductLabelManagementInterface
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var Resolver */
    private $resolver;

    /** @var LabelData */
    private $labelData;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param Resolver $resolver
     * @param LabelData $labelData
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        Resolver $resolver,
        LabelData $labelData
    ) {
        $this->productRepository = $productRepository;
        $this->resolver = $resolver;
        $this->labelData = $labelData;
    }

    /**
     * @inheritdoc
     */
    public function getBySku(string $sku): array
    {
        $product = $this->productRepository->get($sku);
        $labels = $this->resolver->getLabelsForProduct($product);

        return array_map(function (array $label): array {
            return $this->labelData->format($label);
        }, $labels);
    }
}
