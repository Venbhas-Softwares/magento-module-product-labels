<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Model\Label\Source;

use Magento\Store\Ui\Component\Listing\Column\Store\Options as StoreColumnOptions;

/**
 * Store view options for the product label admin form.
 */
class StoreOptions extends StoreColumnOptions
{
    public const ALL_STORE_VIEWS = '0';

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $this->currentOptions['All Store Views']['label'] = __('All Store Views');
        $this->currentOptions['All Store Views']['value'] = self::ALL_STORE_VIEWS;

        $this->generateCurrentOptions();

        $this->options = array_values($this->currentOptions);

        return $this->options;
    }
}
