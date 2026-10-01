<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Model\ResourceModel;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Venbhas\ProductLabels\Model\Label as LabelModel;

/**
 * Product label resource model.
 */
class Label extends AbstractDb
{
    /** @var string */
    private const STORE_TABLE = 'venbhas_product_labels_store';

    /** @var CacheInterface */
    private $cache;

    /**
     * @param Context $context
     * @param CacheInterface $cache
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        CacheInterface $cache,
        ?string $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->cache = $cache;
    }

    /**
     * @inheritdoc
     */
    protected function _construct(): void
    {
        $this->_init('venbhas_product_labels', 'label_id');
    }

    /**
     * @inheritdoc
     */
    protected function _afterLoad(AbstractModel $object): AbstractDb
    {
        if ($object->getId()) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from($this->getTable(self::STORE_TABLE), 'store_id')
                ->where('label_id = ?', (int) $object->getId());
            $storeIds = array_map('intval', $connection->fetchCol($select));
            $object->setData('store_ids', $storeIds);
        }

        return parent::_afterLoad($object);
    }

    /**
     * @inheritdoc
     */
    protected function _afterSave(AbstractModel $object): AbstractDb
    {
        $this->saveStoreRelations($object);
        $this->invalidateLabelCache();
        return parent::_afterSave($object);
    }

    /**
     * @inheritdoc
     */
    protected function _afterDelete(AbstractModel $object): AbstractDb
    {
        $this->invalidateLabelCache();
        return parent::_afterDelete($object);
    }

    /**
     * Persist store view assignments.
     *
     * @param AbstractModel $object
     * @return void
     */
    private function saveStoreRelations(AbstractModel $object): void
    {
        if (!$object->getId()) {
            return;
        }

        $connection = $this->getConnection();
        $table = $this->getTable(self::STORE_TABLE);
        $labelId = (int) $object->getId();

        $connection->delete($table, ['label_id = ?' => $labelId]);

        $storeIds = $object->getData('store_ids');
        if (!is_array($storeIds)) {
            $storeIds = [];
        }

        $storeIds = array_map('intval', (array) $storeIds);
        $storeIds = array_values(
            array_unique(
                array_filter($storeIds, static fn (int $id): bool => $id > 0)
            )
        );
        if ($storeIds === []) {
            $connection->insert($table, ['label_id' => $labelId, 'store_id' => 0]);
            return;
        }

        foreach ($storeIds as $storeId) {
            $connection->insert($table, [
                'label_id' => $labelId,
                'store_id' => (int) $storeId,
            ]);
        }
    }

    /**
     * Clear cached label resolution results.
     *
     * @return void
     */
    private function invalidateLabelCache(): void
    {
        $this->cache->clean([LabelModel::CACHE_TAG]);
    }
}
