<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Model\Label\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Label shape source model.
 */
class Shape implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'rectangle', 'label' => __('Rectangle')],
            ['value' => 'circle', 'label' => __('Circle')],
            ['value' => 'ribbon', 'label' => __('Ribbon')],
        ];
    }
}
