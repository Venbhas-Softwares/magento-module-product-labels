<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rule\Model\AbstractModel;
use Venbhas\ProductLabels\Api\Data\LabelInterface;
use Venbhas\ProductLabels\Model\Label\Action\CollectionFactory as ActionCollectionFactory;
use Venbhas\ProductLabels\Model\Label\Condition\CombineFactory;
use Venbhas\ProductLabels\Model\Label\IdListFormatter;
use Venbhas\ProductLabels\Model\ResourceModel\Label as LabelResource;

/**
 * Product label rule model.
 */
class Label extends AbstractModel implements LabelInterface, IdentityInterface
{
    public const CACHE_TAG = 'venbhas_product_labels';

    /** @var CombineFactory */
    private $combineFactory;

    /** @var ActionCollectionFactory */
    private $actionCollectionFactory;

    /** @var IdListFormatter */
    private $idListFormatter;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     * @param CombineFactory $combineFactory
     * @param ActionCollectionFactory $actionCollectionFactory
     * @param IdListFormatter $idListFormatter
     * @param LabelResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        CombineFactory $combineFactory,
        ActionCollectionFactory $actionCollectionFactory,
        IdListFormatter $idListFormatter,
        ?LabelResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->combineFactory = $combineFactory;
        $this->actionCollectionFactory = $actionCollectionFactory;
        $this->idListFormatter = $idListFormatter;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @inheritdoc
     */
    public function beforeSave()
    {
        parent::beforeSave();

        if ($this->hasCustomerGroupIds()) {
            $this->setData(
                self::CUSTOMER_GROUP_IDS,
                $this->idListFormatter->implodeIds($this->getData(self::CUSTOMER_GROUP_IDS))
            );
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function validateData(\Magento\Framework\DataObject $dataObject)
    {
        $result = [];

        if ($dataObject->hasFromDate() && $dataObject->hasToDate()) {
            $fromDate = $dataObject->getFromDate();
            $toDate = $dataObject->getToDate();

            if ($fromDate && $toDate) {
                $fromDate = $fromDate instanceof \DateTimeInterface
                    ? $fromDate
                    : new \DateTime((string) $fromDate);
                $toDate = $toDate instanceof \DateTimeInterface
                    ? $toDate
                    : new \DateTime((string) $toDate);

                if ($fromDate > $toDate) {
                    $result[] = __('End Date must follow Start Date.');
                }
            }
        }

        return $result !== [] ? $result : true;
    }

    /**
     * @inheritdoc
     */
    protected function _construct(): void
    {
        $this->_init(LabelResource::class);
    }

    /**
     * @inheritdoc
     */
    public function getConditionsInstance()
    {
        return $this->combineFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function getActionsInstance()
    {
        return $this->actionCollectionFactory->create();
    }

    /**
     * Getter for conditions field set ID.
     *
     * @param string $formName
     * @return string
     */
    public function getConditionsFieldSetId($formName = ''): string
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }

    /**
     * @inheritdoc
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG, 'venbhas_label_' . $this->getId()];
    }

    /**
     * @inheritdoc
     */
    public function getLabelId(): ?int
    {
        $id = $this->getData(self::LABEL_ID);
        return $id !== null ? (int) $id : null;
    }

    /**
     * @inheritdoc
     */
    public function setLabelId(int $labelId): LabelInterface
    {
        return $this->setData(self::LABEL_ID, $labelId);
    }

    /**
     * @inheritdoc
     */
    public function getName(): ?string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritdoc
     */
    public function setName(string $name): LabelInterface
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getLabelType(): ?string
    {
        return $this->getData(self::LABEL_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setLabelType(string $labelType): LabelInterface
    {
        return $this->setData(self::LABEL_TYPE, $labelType);
    }

    /**
     * @inheritdoc
     */
    public function getTextContent(): ?string
    {
        return $this->getData(self::TEXT_CONTENT);
    }

    /**
     * @inheritdoc
     */
    public function setTextContent(?string $textContent): LabelInterface
    {
        return $this->setData(self::TEXT_CONTENT, $textContent);
    }

    /**
     * @inheritdoc
     */
    public function getDiscountDisplay(): ?string
    {
        return $this->getData(self::DISCOUNT_DISPLAY);
    }

    /**
     * @inheritdoc
     */
    public function setDiscountDisplay(?string $discountDisplay): LabelInterface
    {
        return $this->setData(self::DISCOUNT_DISPLAY, $discountDisplay);
    }

    /**
     * @inheritdoc
     */
    public function getBgColor(): ?string
    {
        return $this->getData(self::BG_COLOR);
    }

    /**
     * @inheritdoc
     */
    public function setBgColor(?string $bgColor): LabelInterface
    {
        return $this->setData(self::BG_COLOR, $bgColor);
    }

    /**
     * @inheritdoc
     */
    public function getTextColor(): ?string
    {
        return $this->getData(self::TEXT_COLOR);
    }

    /**
     * @inheritdoc
     */
    public function setTextColor(?string $textColor): LabelInterface
    {
        return $this->setData(self::TEXT_COLOR, $textColor);
    }

    /**
     * @inheritdoc
     */
    public function getShape(): ?string
    {
        return $this->getData(self::SHAPE);
    }

    /**
     * @inheritdoc
     */
    public function setShape(string $shape): LabelInterface
    {
        return $this->setData(self::SHAPE, $shape);
    }

    /**
     * @inheritdoc
     */
    public function getFontSize(): ?int
    {
        $value = $this->getData(self::FONT_SIZE);
        return $value !== null ? (int) $value : null;
    }

    /**
     * @inheritdoc
     */
    public function setFontSize(int $fontSize): LabelInterface
    {
        return $this->setData(self::FONT_SIZE, $fontSize);
    }

    /**
     * @inheritdoc
     */
    public function getPosition(): ?string
    {
        return $this->getData(self::POSITION);
    }

    /**
     * @inheritdoc
     */
    public function setPosition(string $position): LabelInterface
    {
        return $this->setData(self::POSITION, $position);
    }

    /**
     * @inheritdoc
     */
    public function getPositionX(): ?int
    {
        $value = $this->getData(self::POSITION_X);
        return $value !== null ? (int) $value : null;
    }

    /**
     * @inheritdoc
     */
    public function setPositionX(int $positionX): LabelInterface
    {
        return $this->setData(self::POSITION_X, $positionX);
    }

    /**
     * @inheritdoc
     */
    public function getPositionY(): ?int
    {
        $value = $this->getData(self::POSITION_Y);
        return $value !== null ? (int) $value : null;
    }

    /**
     * @inheritdoc
     */
    public function setPositionY(int $positionY): LabelInterface
    {
        return $this->setData(self::POSITION_Y, $positionY);
    }

    /**
     * @inheritdoc
     */
    public function getPriority(): ?int
    {
        $value = $this->getData(self::PRIORITY);
        return $value !== null ? (int) $value : null;
    }

    /**
     * @inheritdoc
     */
    public function setPriority(int $priority): LabelInterface
    {
        return $this->setData(self::PRIORITY, $priority);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroupIds(): ?string
    {
        $value = $this->getData(self::CUSTOMER_GROUP_IDS);
        if (is_array($value)) {
            return $this->idListFormatter->implodeIds($value);
        }

        return $value !== null ? (string) $value : null;
    }

    /**
     * @inheritdoc
     */
    public function setCustomerGroupIds(string|array|null $customerGroupIds): LabelInterface
    {
        return $this->setData(self::CUSTOMER_GROUP_IDS, $this->idListFormatter->implodeIds($customerGroupIds));
    }

    /**
     * @inheritdoc
     */
    public function getIsActive(): ?int
    {
        $value = $this->getData(self::IS_ACTIVE);
        return $value !== null ? (int) $value : null;
    }

    /**
     * @inheritdoc
     */
    public function setIsActive(int $isActive): LabelInterface
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * @inheritdoc
     */
    public function getFromDate(): ?string
    {
        return $this->getData(self::FROM_DATE);
    }

    /**
     * @inheritdoc
     */
    public function setFromDate(?string $fromDate): LabelInterface
    {
        return $this->setData(self::FROM_DATE, $fromDate);
    }

    /**
     * @inheritdoc
     */
    public function getToDate(): ?string
    {
        return $this->getData(self::TO_DATE);
    }

    /**
     * @inheritdoc
     */
    public function setToDate(?string $toDate): LabelInterface
    {
        return $this->setData(self::TO_DATE, $toDate);
    }

    /**
     * @inheritdoc
     */
    public function getConditionsSerialized(): ?string
    {
        return $this->getData(self::CONDITIONS_SERIALIZED);
    }

    /**
     * @inheritdoc
     */
    public function setConditionsSerialized(?string $conditionsSerialized): LabelInterface
    {
        return $this->setData(self::CONDITIONS_SERIALIZED, $conditionsSerialized);
    }

    /**
     * @inheritdoc
     */
    public function getStoreIds(): ?array
    {
        $storeIds = $this->getData(self::STORE_IDS);
        if ($storeIds === null) {
            return null;
        }
        if (!is_array($storeIds)) {
            return [(int) $storeIds];
        }
        return array_map('intval', $storeIds);
    }

    /**
     * @inheritdoc
     */
    public function setStoreIds(?array $storeIds): LabelInterface
    {
        return $this->setData(self::STORE_IDS, $storeIds);
    }

    /**
     * Get attributes collected for condition validation.
     *
     * @return array<string, bool>
     */
    public function getCollectedAttributes(): array
    {
        $attributes = $this->getData('collected_attributes');
        return is_array($attributes) ? $attributes : [];
    }

    /**
     * Set attributes collected for condition validation.
     *
     * @param array<string, bool> $attributes
     * @return $this
     */
    public function setCollectedAttributes(array $attributes): self
    {
        return $this->setData('collected_attributes', $attributes);
    }
}
