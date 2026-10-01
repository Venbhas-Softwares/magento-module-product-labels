<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Model\Label\Condition;

use Magento\Rule\Model\Condition\Context;

/**
 * Product label conditions combine.
 */
class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /** @var ProductFactory */
    private $productFactory;

    /**
     * @param Context $context
     * @param ProductFactory $productFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductFactory $productFactory,
        array $data = []
    ) {
        $this->productFactory = $productFactory;
        parent::__construct($context, $data);
        $this->setType(self::class);
    }

    /**
     * @inheritdoc
     */
    public function getNewChildSelectOptions(): array
    {
        $productAttributes = $this->productFactory->create()->loadAttributeOptions()->getAttributeOption();
        $attributes = [];
        foreach ($productAttributes as $code => $label) {
            $attributes[] = [
                'value' => Product::class . '|' . $code,
                'label' => $label,
            ];
        }

        $conditions = parent::getNewChildSelectOptions();
        return array_merge_recursive(
            $conditions,
            [
                [
                    'value' => self::class,
                    'label' => __('Conditions combination'),
                ],
                ['label' => __('Product Attribute'), 'value' => $attributes],
            ]
        );
    }

    /**
     * Collect attributes required by nested conditions for product validation.
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }

        return $this;
    }
}
