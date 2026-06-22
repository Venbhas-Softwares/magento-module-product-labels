<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Controller\Adminhtml\Label;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Ui\Component\MassAction\Filter;
use Venbhas\ProductLabels\Model\ResourceModel\Label\CollectionFactory as LabelCollectionFactory;

/**
 * Mass update product label status controller.
 */
class MassStatus extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Venbhas_ProductLabels::label_save';

    /** @var Filter */
    private $filter;

    /** @var LabelCollectionFactory */
    private $collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param LabelCollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        LabelCollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Execute action.
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $status = (int) $this->getRequest()->getParam('status');
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $size = $collection->getSize();
            foreach ($collection->getItems() as $label) {
                $label->setIsActive($status);
                $label->save();
            }
            $this->messageManager->addSuccessMessage(__('A total of %1 product label(s) have been updated.', $size));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $resultRedirect->setPath('*/*/');
    }
}
