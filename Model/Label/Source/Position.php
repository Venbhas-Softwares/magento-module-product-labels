<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Model\Label\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Label position source model.
 */
class Position implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'top-left', 'label' => __('Top Left')],
            ['value' => 'top-right', 'label' => __('Top Right')],
            ['value' => 'bottom-left', 'label' => __('Bottom Left')],
            ['value' => 'bottom-right', 'label' => __('Bottom Right')],
            ['value' => 'top-center', 'label' => __('Top Center')],
            ['value' => 'bottom-center', 'label' => __('Bottom Center')],
        ];
    }
}
