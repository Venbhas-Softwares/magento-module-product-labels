<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Controller\Adminhtml\Label;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\ConditionInterface;
use Venbhas\ProductLabels\Model\Label;

/**
 * Render new condition HTML for the label form.
 */
class NewConditionHtml extends Action implements HttpPostActionInterface, HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Venbhas_ProductLabels::label_save';

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $objectId = $this->getRequest()->getParam('id');
        $formNamespace = $this->getRequest()->getParam('form_namespace');
        $types = explode('|', str_replace('-', '/', (string) $this->getRequest()->getParam('type', '')));
        $objectType = $types[0] ?? '';
        $responseBody = '';

        if (!$objectType
            || !class_exists($objectType)
            || !in_array(ConditionInterface::class, class_implements($objectType), true)
        ) {
            $this->getResponse()->setBody($responseBody);
            return;
        }

        $conditionModel = $this->_objectManager->create($objectType)
            ->setId($objectId)
            ->setType($objectType)
            ->setRule($this->_objectManager->create(Label::class))
            ->setPrefix('conditions');

        if (!empty($types[1])) {
            $conditionModel->setAttribute($types[1]);
        }

        if ($conditionModel instanceof AbstractCondition) {
            $conditionModel->setJsFormObject($this->getRequest()->getParam('form'));
            $conditionModel->setFormName($formNamespace);
            $responseBody = $conditionModel->asHtmlRecursive();
        }

        $this->getResponse()->setBody($responseBody);
    }
}
