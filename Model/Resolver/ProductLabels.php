<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Model\Resolver;

use Magento\Catalog\Model\Product;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Venbhas\ProductLabels\Model\Config;
use Venbhas\ProductLabels\Model\Label\Resolver as LabelResolver;
use Venbhas\ProductLabels\Model\Resolver\DataProvider\LabelData;

/**
 * GraphQL productLabels field resolver.
 */
class ProductLabels implements ResolverInterface
{
    /** @var LabelResolver */
    private $labelResolver;

    /** @var LabelData */
    private $labelData;

    /** @var Config */
    private $config;

    /**
     * @param LabelResolver $labelResolver
     * @param LabelData $labelData
     * @param Config $config
     */
    public function __construct(
        LabelResolver $labelResolver,
        LabelData $labelData,
        Config $config
    ) {
        $this->labelResolver = $labelResolver;
        $this->labelData = $labelData;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if (!$this->config->isEnabled()) {
            return [];
        }

        if (!isset($value['model']) || !$value['model'] instanceof Product) {
            return [];
        }

        $labels = $this->labelResolver->getLabelsForProduct($value['model']);
        return $this->labelData->formatList($labels);
    }
}
