<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Model\Label\Condition;

use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\Product\AbstractProduct;
use Magento\CatalogRule\Model\Rule\Condition\Product as CatalogRuleProduct;

/**
 * Product label product condition.
 */
class Product extends CatalogRuleProduct
{
    /**
     * Validate using the base rule product condition.
     *
     * Catalog rule's override returns false when an attribute is not already
     * present on the model, which breaks frontend validation after
     * collectValidatedAttributes() has loaded store-scoped values.
     *
     * @param AbstractModel $model
     * @return bool
     */
    public function validate(AbstractModel $model)
    {
        if ($this->getAttribute() === 'sku') {
            return $this->validateAttribute((string) $model->getSku());
        }

        $reflectionMethod = new \ReflectionMethod(AbstractProduct::class, 'validate');
        return (bool) $reflectionMethod->invoke($this, $model);
    }
}
