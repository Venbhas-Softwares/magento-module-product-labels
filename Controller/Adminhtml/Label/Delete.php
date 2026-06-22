<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Controller\Adminhtml\Label;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Venbhas\ProductLabels\Model\LabelFactory;
use Venbhas\ProductLabels\Model\ResourceModel\Label as LabelResource;

/**
 * Delete product label controller.
 */
class Delete extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Venbhas_ProductLabels::label_delete';

    /** @var LabelFactory */
    private $labelFactory;

    /** @var LabelResource */
    private $labelResource;

    /**
     * @param Context $context
     * @param LabelFactory $labelFactory
     * @param LabelResource $labelResource
     */
    public function __construct(
        Context $context,
        LabelFactory $labelFactory,
        LabelResource $labelResource
    ) {
        parent::__construct($context);
        $this->labelFactory = $labelFactory;
        $this->labelResource = $labelResource;
    }

    /**
     * Execute action.
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = (int) $this->getRequest()->getParam('label_id');
        if (!$id) {
            $this->messageManager->addErrorMessage(__('We can\'t find a product label to delete.'));
            return $resultRedirect->setPath('*/*/');
        }
        $model = $this->labelFactory->create();
        $this->labelResource->load($model, $id);
        if (!$model->getId()) {
            $this->messageManager->addErrorMessage(__('This product label no longer exists.'));
            return $resultRedirect->setPath('*/*/');
        }
        try {
            $this->labelResource->delete($model);
            $this->messageManager->addSuccessMessage(__('The product label has been deleted.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/edit', ['label_id' => $id]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
