<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Log level source model.
 */
class LogLevel implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'info', 'label' => __('Info')],
            ['value' => 'warning', 'label' => __('Warning')],
            ['value' => 'debug', 'label' => __('Debug')],
        ];
    }
}
