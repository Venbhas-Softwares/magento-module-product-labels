<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Venbhas\ProductLabels\Api\Data\LabelInterface;

/**
 * Product label repository interface.
 */
interface LabelRepositoryInterface
{
    /**
     * Get a product label by ID.
     *
     * @param int $labelId
     * @return \Venbhas\ProductLabels\Api\Data\LabelInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $labelId): LabelInterface;

    /**
     * Save a product label.
     *
     * @param \Venbhas\ProductLabels\Api\Data\LabelInterface $label
     * @return \Venbhas\ProductLabels\Api\Data\LabelInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(LabelInterface $label): LabelInterface;

    /**
     * Delete a product label.
     *
     * @param \Venbhas\ProductLabels\Api\Data\LabelInterface $label
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(LabelInterface $label): bool;

    /**
     * Delete a product label by ID.
     *
     * @param int $labelId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById(int $labelId): bool;

    /**
     * Get a list of product labels matching search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
