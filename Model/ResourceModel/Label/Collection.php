<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Model\ResourceModel\Label;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Venbhas\ProductLabels\Model\Label;
use Venbhas\ProductLabels\Model\ResourceModel\Label as LabelResource;

/**
 * Product label collection.
 */
class Collection extends AbstractCollection
{
    /** @var string */
    protected $_idFieldName = 'label_id';

    /**
     * @inheritdoc
     */
    protected function _construct(): void
    {
        $this->_init(Label::class, LabelResource::class);
    }

    /**
     * @inheritdoc
     */
    protected function _afterLoad()
    {
        $labelIds = $this->getColumnValues('label_id');
        if ($labelIds !== []) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from($this->getTable('venbhas_product_labels_store'), ['label_id', 'store_id'])
                ->where('label_id IN (?)', $labelIds);
            $rows = $connection->fetchAll($select);

            $storeData = [];
            foreach ($rows as $row) {
                $storeData[(int) $row['label_id']][] = (int) $row['store_id'];
            }

            foreach ($this as $item) {
                $item->setData('store_ids', $storeData[(int) $item->getId()] ?? [0]);
            }
        }

        return parent::_afterLoad();
    }

    /**
     * Filter active labels for a store and current date.
     *
     * @param int $storeId
     * @param string|null $date Y-m-d
     * @return $this
     */
    public function addActiveFilter(int $storeId, ?string $date = null): self
    {
        $date = $date ?? date('Y-m-d');

        $this->addFieldToFilter('is_active', ['eq' => 1]);
        $this->addFieldToFilter(
            ['from_date', 'from_date'],
            [
                ['null' => true],
                ['lteq' => $date],
            ]
        );
        $this->addFieldToFilter(
            ['to_date', 'to_date'],
            [
                ['null' => true],
                ['gteq' => $date],
            ]
        );

        $this->getSelect()->join(
            ['store_table' => $this->getTable('venbhas_product_labels_store')],
            'main_table.label_id = store_table.label_id',
            []
        )->where(
            'store_table.store_id IN (?)',
            [0, $storeId]
        )->group('main_table.label_id');

        $this->setOrder('priority', self::SORT_ORDER_ASC);

        return $this;
    }
}
