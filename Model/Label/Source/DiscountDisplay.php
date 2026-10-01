<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Model\Label\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Venbhas\ProductLabels\Api\Data\LabelInterface;

/**
 * Discount display source model.
 */
class DiscountDisplay implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => LabelInterface::DISCOUNT_PERCENT, 'label' => __('Percentage (%)')],
            ['value' => LabelInterface::DISCOUNT_AMOUNT, 'label' => __('Amount')],
        ];
    }
}
