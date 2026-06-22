<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Model\Label\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Active status source model.
 */
class IsActive implements OptionSourceInterface
{
    public const STATUS_DISABLED = 0;
    public const STATUS_ENABLED = 1;

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => (string) self::STATUS_DISABLED, 'label' => __('Disabled')],
            ['value' => (string) self::STATUS_ENABLED, 'label' => __('Enabled')],
        ];
    }
}
