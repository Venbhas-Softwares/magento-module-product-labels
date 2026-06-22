<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Venbhas\ProductLabels\Api\Data\LabelInterface;
use Venbhas\ProductLabels\Api\LabelRepositoryInterface;
use Venbhas\ProductLabels\Model\ResourceModel\Label as LabelResource;
use Venbhas\ProductLabels\Model\ResourceModel\Label\CollectionFactory;

/**
 * Product label repository.
 */
class LabelRepository implements LabelRepositoryInterface
{
    /** @var LabelFactory */
    private $labelFactory;

    /** @var LabelResource */
    private $labelResource;

    /** @var CollectionFactory */
    private $collectionFactory;

    /** @var SearchResultsInterfaceFactory */
    private $searchResultsFactory;

    /** @var CollectionProcessorInterface */
    private $collectionProcessor;

    /**
     * @param LabelFactory $labelFactory
     * @param LabelResource $labelResource
     * @param CollectionFactory $collectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        LabelFactory $labelFactory,
        LabelResource $labelResource,
        CollectionFactory $collectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->labelFactory = $labelFactory;
        $this->labelResource = $labelResource;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritdoc
     */
    public function getById(int $labelId): LabelInterface
    {
        $label = $this->labelFactory->create();
        $this->labelResource->load($label, $labelId);
        if (!$label->getId()) {
            throw new NoSuchEntityException(__('Product label with id "%1" does not exist.', $labelId));
        }
        return $label;
    }

    /**
     * @inheritdoc
     */
    public function save(LabelInterface $label): LabelInterface
    {
        try {
            $this->labelResource->save($label);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Could not save product label: %1', $exception->getMessage()));
        }
        return $label;
    }

    /**
     * @inheritdoc
     */
    public function delete(LabelInterface $label): bool
    {
        try {
            $this->labelResource->delete($label);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__('Could not delete product label: %1', $exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById(int $labelId): bool
    {
        return $this->delete($this->getById($labelId));
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }
}
