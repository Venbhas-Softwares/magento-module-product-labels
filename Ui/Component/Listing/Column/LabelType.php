<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Venbhas\ProductLabels\Model\Label\Source\LabelType as LabelTypeSource;

/**
 * Label type grid column renderer.
 */
class LabelType extends Column
{
    /** @var LabelTypeSource */
    private $labelTypeSource;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param LabelTypeSource $labelTypeSource
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        LabelTypeSource $labelTypeSource,
        array $components = [],
        array $data = []
    ) {
        $this->labelTypeSource = $labelTypeSource;
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
        foreach ($this->labelTypeSource->toOptionArray() as $option) {
            $options[$option['value']] = $option['label'];
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $type = $item['label_type'] ?? '';
            $item[$this->getData('name')] = $options[$type] ?? $type;
        }

        return $dataSource;
    }
}
