<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Model\Label\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Venbhas\ProductLabels\Api\Data\LabelInterface;

/**
 * Label type source model.
 */
class LabelType implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => LabelInterface::TYPE_TEXT, 'label' => __('Text')],
            ['value' => LabelInterface::TYPE_DISCOUNT, 'label' => __('Discount')],
        ];
    }
}
