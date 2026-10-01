<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Model\Label;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Venbhas\ProductLabels\Api\Data\LabelInterface;
use Venbhas\ProductLabels\Model\Label\IdListFormatter;
use Venbhas\ProductLabels\Model\Label\Source\IsActive;
use Venbhas\ProductLabels\Model\ResourceModel\Label\CollectionFactory as LabelCollectionFactory;

/**
 * Product label form data provider.
 */
class DataProvider extends AbstractDataProvider
{
    /** @var array|null */
    protected $loadedData;

    /** @var DataPersistorInterface */
    private $dataPersistor;

    /** @var RequestInterface */
    private $request;

    /** @var IdListFormatter */
    private $idListFormatter;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param LabelCollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param RequestInterface $request
     * @param IdListFormatter $idListFormatter
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        LabelCollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        RequestInterface $request,
        IdListFormatter $idListFormatter,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->request = $request;
        $this->idListFormatter = $idListFormatter;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @inheritdoc
     */
    public function getData(): array
    {
        if ($this->loadedData !== null) {
            return $this->loadedData;
        }

        $this->loadedData = [];
        $id = (int) $this->request->getParam($this->getRequestFieldName());
        $persistorData = $this->dataPersistor->get('venbhas_product_label');

        if ($id <= 0) {
            $defaults = $this->getNewLabelDefaults();
            if (!empty($persistorData)) {
                $defaults = array_merge($defaults, $persistorData);
                $this->dataPersistor->clear('venbhas_product_label');
            }
            $this->loadedData[''] = $defaults;
            $this->loadedData[0] = $defaults;
            return $this->loadedData;
        }

        $this->collection->addFieldToFilter($this->getPrimaryFieldName(), $id);
        foreach ($this->collection->getItems() as $label) {
            $data = $label->getData();
            $data['is_active'] = (string) (int) ($data['is_active'] ?? IsActive::STATUS_ENABLED);
            $groupIds = (string) ($data['customer_group_ids'] ?? '');
            $data['customer_group_ids'] = $this->idListFormatter->explodeIds($groupIds);
            $data['store_id'] = $this->getStoreIdsForForm($label->getStoreIds() ?? []);
            $this->loadedData[$label->getId()] = $data;
        }

        return $this->loadedData;
    }

    /**
     * Get default values for a new product label.
     *
     * @return array
     */
    private function getNewLabelDefaults(): array
    {
        return [
            'label_id' => null,
            'name' => '',
            'is_active' => (string) IsActive::STATUS_ENABLED,
            'priority' => 0,
            'from_date' => '',
            'to_date' => '',
            'store_id' => ['0'],
            'customer_group_ids' => [],
            'label_type' => LabelInterface::TYPE_TEXT,
            'text_content' => '',
            'discount_display' => LabelInterface::DISCOUNT_PERCENT,
            'bg_color' => '#ff0000',
            'text_color' => '#ffffff',
            'shape' => 'rectangle',
            'font_size' => 12,
            'position' => 'top-left',
            'position_x' => 0,
            'position_y' => 0,
        ];
    }

    /**
     * Map persisted store IDs to multiselect values.
     *
     * @param int[] $storeIds
     * @return int[]
     */
    private function getStoreIdsForForm(array $storeIds): array
    {
        if (empty($storeIds)) {
            return ['0'];
        }

        return array_map('strval', $storeIds);
    }
}
