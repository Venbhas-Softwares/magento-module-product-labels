<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Model\Label;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Venbhas\ProductLabels\Model\Label as LabelModel;

/**
 * Validates label rule conditions against catalog products.
 */
class ProductValidator
{
    /** @var CollectionFactory */
    private $productCollectionFactory;

    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @param CollectionFactory $productCollectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Validate whether a label's conditions match a product.
     *
     * @param LabelModel $label
     * @param Product $product
     * @param int $storeId
     * @return bool
     */
    public function validate(LabelModel $label, Product $product, int $storeId): bool
    {
        $conditions = $label->getConditions();
        if (!$conditions || !$conditions->getConditions()) {
            return true;
        }

        $productId = (int) $product->getId();
        if ($productId <= 0) {
            return false;
        }

        $label->setCollectedAttributes([]);

        $collection = $this->productCollectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addStoreFilter($storeId);
        $collection->addWebsiteFilter((int) $this->storeManager->getStore($storeId)->getWebsiteId());
        $collection->addIdFilter($productId);
        $conditions->collectValidatedAttributes($collection);
        $collection->load();

        $validationProduct = $collection->getItemById($productId);
        if (!$validationProduct || !$validationProduct->getId()) {
            return false;
        }

        $validationProduct->setStoreId($storeId);

        return $label->validate($validationProduct);
    }
}
