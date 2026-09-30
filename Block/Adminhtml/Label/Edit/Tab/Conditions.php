<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Block\Adminhtml\Label\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Venbhas\ProductLabels\Model\Label;

/**
 * Product label conditions tab.
 */
class Conditions extends Generic implements TabInterface
{
    /** @var Fieldset */
    protected $_rendererFieldset;

    /** @var \Magento\Rule\Block\Conditions */
    protected $_conditions;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Rule\Block\Conditions $conditions
     * @param Fieldset $rendererFieldset
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Rule\Block\Conditions $conditions,
        Fieldset $rendererFieldset,
        array $data = []
    ) {
        $this->_rendererFieldset = $rendererFieldset;
        $this->_conditions = $conditions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return (string)__('Conditions');
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return (string)__('Conditions');
    }

    /**
     * @inheritdoc
     */
    public function canShowTab(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getTabClass(): ?string
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getTabUrl(): ?string
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('venbhas_product_label');
        if ($model instanceof Label) {
            $form = $this->addTabToForm($model);
            $this->setForm($form);
        }

        return parent::_prepareForm();
    }

    /**
     * Build the conditions tab form for a label.
     *
     * @param Label $model
     * @param string $fieldsetId
     * @param string $formName
     * @return \Magento\Framework\Data\Form
     */
    protected function addTabToForm(
        Label $model,
        string $fieldsetId = 'conditions_fieldset',
        string $formName = 'venbhas_productlabels_form'
    ) {
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('label_');

        $conditionsFieldSetId = $model->getConditionsFieldSetId($formName);
        $newChildUrl = $this->getUrl(
            'productlabels/label/newConditionHtml/form/' . $conditionsFieldSetId,
            ['form_namespace' => $formName]
        );

        $renderer = $this->getLayout()->createBlock(Fieldset::class);
        $renderer->setTemplate('Magento_CatalogRule::promo/fieldset.phtml')
            ->setNewChildUrl($newChildUrl)
            ->setFieldSetId($conditionsFieldSetId);

        $fieldset = $form->addFieldset(
            $fieldsetId,
            ['legend' => __('Conditions (leave empty to apply to all products)')]
        )->setRenderer($renderer);

        $fieldset->addField(
            'conditions',
            'text',
            [
                'name' => 'conditions',
                'label' => __('Conditions'),
                'title' => __('Conditions'),
                'required' => false,
                'data-form-part' => $formName,
            ]
        )->setRule($model)->setRenderer($this->_conditions);

        $form->setValues($model->getData());
        $this->setConditionFormName($model->getConditions(), $formName, $conditionsFieldSetId);

        return $form;
    }

    /**
     * Apply form metadata to nested rule conditions.
     *
     * @param AbstractCondition $conditions
     * @param string $formName
     * @param string $jsFormName
     * @return void
     */
    private function setConditionFormName(AbstractCondition $conditions, string $formName, ?string $jsFormName): void
    {
        $conditions->setFormName($formName);
        if ($jsFormName !== null) {
            $conditions->setJsFormObject($jsFormName);
        }

        $childConditions = $conditions->getConditions();
        if ($childConditions && is_array($childConditions)) {
            foreach ($childConditions as $condition) {
                $this->setConditionFormName($condition, $formName, $jsFormName);
            }
        }
    }
}
