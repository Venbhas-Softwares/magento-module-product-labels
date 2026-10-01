<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Api\Data;

/**
 * Product label data interface.
 */
interface LabelInterface
{
    public const LABEL_ID = 'label_id';
    public const NAME = 'name';
    public const LABEL_TYPE = 'label_type';
    public const TEXT_CONTENT = 'text_content';
    public const DISCOUNT_DISPLAY = 'discount_display';
    public const BG_COLOR = 'bg_color';
    public const TEXT_COLOR = 'text_color';
    public const SHAPE = 'shape';
    public const FONT_SIZE = 'font_size';
    public const POSITION = 'position';
    public const POSITION_X = 'position_x';
    public const POSITION_Y = 'position_y';
    public const PRIORITY = 'priority';
    public const CUSTOMER_GROUP_IDS = 'customer_group_ids';
    public const IS_ACTIVE = 'is_active';
    public const FROM_DATE = 'from_date';
    public const TO_DATE = 'to_date';
    public const CONDITIONS_SERIALIZED = 'conditions_serialized';
    public const STORE_IDS = 'store_ids';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    public const TYPE_TEXT = 'text';
    public const TYPE_DISCOUNT = 'discount';

    public const DISCOUNT_PERCENT = 'percent';
    public const DISCOUNT_AMOUNT = 'amount';

    /**
     * Get label ID.
     *
     * @return int|null
     */
    public function getLabelId(): ?int;

    /**
     * Set label ID.
     *
     * @param int $labelId
     * @return $this
     */
    public function setLabelId(int $labelId): self;

    /**
     * Get label name.
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Set label name.
     *
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self;

    /**
     * Get label type.
     *
     * @return string|null
     */
    public function getLabelType(): ?string;

    /**
     * Set label type.
     *
     * @param string $labelType
     * @return $this
     */
    public function setLabelType(string $labelType): self;

    /**
     * Get text content.
     *
     * @return string|null
     */
    public function getTextContent(): ?string;

    /**
     * Set text content.
     *
     * @param string|null $textContent
     * @return $this
     */
    public function setTextContent(?string $textContent): self;

    /**
     * Get discount display format.
     *
     * @return string|null
     */
    public function getDiscountDisplay(): ?string;

    /**
     * Set discount display format.
     *
     * @param string|null $discountDisplay
     * @return $this
     */
    public function setDiscountDisplay(?string $discountDisplay): self;

    /**
     * Get background color.
     *
     * @return string|null
     */
    public function getBgColor(): ?string;

    /**
     * Set background color.
     *
     * @param string|null $bgColor
     * @return $this
     */
    public function setBgColor(?string $bgColor): self;

    /**
     * Get text color.
     *
     * @return string|null
     */
    public function getTextColor(): ?string;

    /**
     * Set text color.
     *
     * @param string|null $textColor
     * @return $this
     */
    public function setTextColor(?string $textColor): self;

    /**
     * Get label shape.
     *
     * @return string|null
     */
    public function getShape(): ?string;

    /**
     * Set label shape.
     *
     * @param string $shape
     * @return $this
     */
    public function setShape(string $shape): self;

    /**
     * Get font size.
     *
     * @return int|null
     */
    public function getFontSize(): ?int;

    /**
     * Set font size.
     *
     * @param int $fontSize
     * @return $this
     */
    public function setFontSize(int $fontSize): self;

    /**
     * Get label position.
     *
     * @return string|null
     */
    public function getPosition(): ?string;

    /**
     * Set label position.
     *
     * @param string $position
     * @return $this
     */
    public function setPosition(string $position): self;

    /**
     * Get horizontal position offset.
     *
     * @return int|null
     */
    public function getPositionX(): ?int;

    /**
     * Set horizontal position offset.
     *
     * @param int $positionX
     * @return $this
     */
    public function setPositionX(int $positionX): self;

    /**
     * Get vertical position offset.
     *
     * @return int|null
     */
    public function getPositionY(): ?int;

    /**
     * Set vertical position offset.
     *
     * @param int $positionY
     * @return $this
     */
    public function setPositionY(int $positionY): self;

    /**
     * Get label priority.
     *
     * @return int|null
     */
    public function getPriority(): ?int;

    /**
     * Set label priority.
     *
     * @param int $priority
     * @return $this
     */
    public function setPriority(int $priority): self;

    /**
     * Get allowed customer group IDs.
     *
     * @return string|null
     */
    public function getCustomerGroupIds(): ?string;

    /**
     * Set allowed customer group IDs.
     *
     * @param string|array|null $customerGroupIds
     * @return $this
     */
    public function setCustomerGroupIds(string|array|null $customerGroupIds): self;

    /**
     * Get active status.
     *
     * @return int|null
     */
    public function getIsActive(): ?int;

    /**
     * Set active status.
     *
     * @param int $isActive
     * @return $this
     */
    public function setIsActive(int $isActive): self;

    /**
     * Get active-from date.
     *
     * @return string|null
     */
    public function getFromDate(): ?string;

    /**
     * Set active-from date.
     *
     * @param string|null $fromDate
     * @return $this
     */
    public function setFromDate(?string $fromDate): self;

    /**
     * Get active-to date.
     *
     * @return string|null
     */
    public function getToDate(): ?string;

    /**
     * Set active-to date.
     *
     * @param string|null $toDate
     * @return $this
     */
    public function setToDate(?string $toDate): self;

    /**
     * Get serialized rule conditions.
     *
     * @return string|null
     */
    public function getConditionsSerialized(): ?string;

    /**
     * Set serialized rule conditions.
     *
     * @param string|null $conditionsSerialized
     * @return $this
     */
    public function setConditionsSerialized(?string $conditionsSerialized): self;

    /**
     * Get assigned store IDs.
     *
     * @return int[]|null
     */
    public function getStoreIds(): ?array;

    /**
     * Set assigned store IDs.
     *
     * @param int[]|null $storeIds
     * @return $this
     */
    public function setStoreIds(?array $storeIds): self;
}
