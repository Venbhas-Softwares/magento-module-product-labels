<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Plugin\CatalogRule\Condition;

use Magento\CatalogRule\Model\Rule\Condition\Product;
use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\Product\AbstractProduct;
use Venbhas\ProductLabels\Model\Label;

/**
 * Use base product rule validation for product label conditions.
 */
class ProductPlugin
{
    /**
     * Validate label rule conditions with attribute maps from collectValidatedAttributes().
     *
     * @param Product $subject
     * @param callable $proceed
     * @param AbstractModel $model
     * @return bool
     */
    public function aroundValidate(Product $subject, callable $proceed, AbstractModel $model): bool
    {
        if ($subject->getRule() instanceof Label) {
            $reflectionMethod = new \ReflectionMethod(AbstractProduct::class, 'validate');
            return (bool) $reflectionMethod->invoke($subject, $model);
        }

        return (bool) $proceed($model);
    }
}
