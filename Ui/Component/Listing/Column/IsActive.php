<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Venbhas\ProductLabels\Model\Label\Source\IsActive as IsActiveSource;

/**
 * Is active grid column renderer.
 */
class IsActive extends Column
{
    /** @var IsActiveSource */
    private $isActiveSource;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param IsActiveSource $isActiveSource
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        IsActiveSource $isActiveSource,
        array $components = [],
        array $data = []
    ) {
        $this->isActiveSource = $isActiveSource;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $options = [];
        foreach ($this->isActiveSource->toOptionArray() as $option) {
            $options[$option['value']] = $option['label'];
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $status = (string) (int) ($item['is_active'] ?? 0);
            $item[$this->getData('name')] = $options[$status] ?? $status;
        }

        return $dataSource;
    }
}
