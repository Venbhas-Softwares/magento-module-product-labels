<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Controller\Adminhtml\Label;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\FilterInput;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Venbhas\ProductLabels\Api\Data\LabelInterface;
use Venbhas\ProductLabels\Model\Label\IdListFormatter;
use Venbhas\ProductLabels\Model\Label\Source\IsActive;
use Venbhas\ProductLabels\Model\LabelFactory;
use Venbhas\ProductLabels\Model\ResourceModel\Label as LabelResource;

/**
 * Save product label controller.
 */
class Save extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Venbhas_ProductLabels::label_save';

    /** @var LabelFactory */
    private $labelFactory;

    /** @var LabelResource */
    private $labelResource;

    /** @var DataPersistorInterface */
    private $dataPersistor;

    /** @var Date */
    private $dateFilter;

    /** @var TimezoneInterface */
    private $localeDate;

    /** @var IdListFormatter */
    private $idListFormatter;

    /**
     * @param Context $context
     * @param LabelFactory $labelFactory
     * @param LabelResource $labelResource
     * @param DataPersistorInterface $dataPersistor
     * @param Date $dateFilter
     * @param TimezoneInterface $localeDate
     * @param IdListFormatter $idListFormatter
     */
    public function __construct(
        Context $context,
        LabelFactory $labelFactory,
        LabelResource $labelResource,
        DataPersistorInterface $dataPersistor,
        Date $dateFilter,
        TimezoneInterface $localeDate,
        IdListFormatter $idListFormatter
    ) {
        parent::__construct($context);
        $this->labelFactory = $labelFactory;
        $this->labelResource = $labelResource;
        $this->dataPersistor = $dataPersistor;
        $this->dateFilter = $dateFilter;
        $this->localeDate = $localeDate;
        $this->idListFormatter = $idListFormatter;
    }

    /**
     * @inheritdoc
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequestData();

        if ($data === []) {
            $this->messageManager->addErrorMessage(__('Invalid request data.'));
            return $resultRedirect->setPath('*/*/');
        }

        if (!empty($data['data']) && is_array($data['data'])) {
            $data = array_merge($data, $data['data']);
            unset($data['data']);
        }

        $id = (int) ($data['label_id'] ?? 0);
        $model = $this->labelFactory->create();

        if ($id) {
            $this->labelResource->load($model, $id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This product label no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
        }

        try {
            if (isset($data['rule']['conditions'])) {
                $data['conditions'] = $data['rule']['conditions'];
                unset($data['rule']);
            }

            unset($data['conditions_serialized'], $data['actions_serialized']);

            $data = $this->normalizeData($data);
            $validateResult = $model->validateData(new \Magento\Framework\DataObject($data));
            if ($validateResult !== true) {
                foreach ($validateResult as $errorMessage) {
                    $this->messageManager->addErrorMessage($errorMessage);
                }
                $this->dataPersistor->set('venbhas_product_label', $data);
                return $resultRedirect->setPath($id ? '*/*/edit' : '*/*/new', $id ? ['label_id' => $id] : []);
            }

            $model->addData($this->getPersistableData($data));
            $model->loadPost($data);
            $model->setData('store_ids', $data['store_ids'] ?? []);
            $this->labelResource->save($model);

            $this->messageManager->addSuccessMessage(__('You saved the product label.'));
            $this->dataPersistor->clear('venbhas_product_label');

            if ($this->getRequest()->getParam('back')) {
                return $resultRedirect->setPath('*/*/edit', ['label_id' => $model->getId()]);
            }

            return $resultRedirect->setPath('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong while saving the product label.'));
        }

        $this->dataPersistor->set('venbhas_product_label', $data);
        return $resultRedirect->setPath($id ? '*/*/edit' : '*/*/new', $id ? ['label_id' => $id] : []);
    }

    /**
     * Get request payload from JSON or form post.
     *
     * @return array
     */
    private function getRequestData(): array
    {
        $request = $this->getRequest();
        $data = $request->getPostValue() ?? [];

        $content = (string) $request->getContent();
        $contentType = (string) $request->getHeader('Content-Type');
        if ($content !== '' && str_contains($contentType, 'application/json')) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                // POST params carry htmlContent fields such as rule conditions.
                $data = array_merge($decoded, $data);
            }
        }

        if (!empty($data['data']) && is_array($data['data'])) {
            $data = array_merge($data, $data['data']);
            unset($data['data']);
        }

        return $data;
    }

    /**
     * Normalize and sanitize label data from the request.
     *
     * @param array $data
     * @return array
     */
    private function normalizeData(array $data): array
    {
        $filterValues = [];
        if (!empty($data['from_date'])) {
            $filterValues['from_date'] = $this->dateFilter;
        }
        if (!empty($data['to_date'])) {
            $filterValues['to_date'] = $this->dateFilter;
        }

        if ($filterValues !== []) {
            $inputFilter = new FilterInput($filterValues, [], $data);
            $data = $inputFilter->getUnescaped();
        }

        $data['is_active'] = (int) ($data['is_active'] ?? IsActive::STATUS_ENABLED);
        $data['priority'] = (int) ($data['priority'] ?? 0);
        $data['font_size'] = (int) ($data['font_size'] ?? 12);
        $data['position_x'] = (int) ($data['position_x'] ?? 0);
        $data['position_y'] = (int) ($data['position_y'] ?? 0);
        $data['label_type'] = (string) ($data['label_type'] ?? LabelInterface::TYPE_TEXT);
        $data['shape'] = (string) ($data['shape'] ?? 'rectangle');
        $data['position'] = (string) ($data['position'] ?? 'top-left');

        $data['customer_group_ids'] = $this->idListFormatter->implodeIds(
            $data['customer_group_ids'] ?? ''
        );
        $storeIds = $this->normalizeStoreIds($data['store_id'] ?? []);

$data['store_ids'] = $storeIds;
unset($data['store_id']);

        if (empty($id = (int) ($data['label_id'] ?? 0))) {
            unset($data['label_id']);
        }

        return $data;
    }

    /**
     * Keep only fields that should be persisted on the label model.
     *
     * @param array $data
     * @return array
     */
    private function getPersistableData(array $data): array
    {
        $fields = [
            'name',
            'label_type',
            'text_content',
            'discount_display',
            'bg_color',
            'text_color',
            'shape',
            'font_size',
            'position',
            'position_x',
            'position_y',
            'priority',
            'customer_group_ids',
            'is_active',
            'from_date',
            'to_date',
            'store_ids',
        ];

        return array_intersect_key($data, array_flip($fields));
    }

    /**
     * Normalize store view IDs from the request.
     *
     * @param mixed $storeIds
     * @return int[]
     */
    private function normalizeStoreIds($storeIds): array
{
    if (is_string($storeIds)) {
        $storeIds = explode(',', $storeIds);
    }

    if (!is_array($storeIds)) {
        return [0];
    }

    $storeIds = array_map('intval', $storeIds);

    // If All Store Views is selected together with others,
    // keep only All Store Views.
    if (in_array(0, $storeIds, true)) {
        return [0];
    }

    return array_values(array_unique($storeIds));
}
}
