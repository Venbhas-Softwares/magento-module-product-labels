<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Model\Label;

use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Venbhas\ProductLabels\Api\Data\LabelInterface;

/**
 * Discount label text calculator.
 */
class DiscountCalculator
{
    /** @var PriceCurrencyInterface */
    private $priceCurrency;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(PriceCurrencyInterface $priceCurrency)
    {
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Calculate display text for a discount label.
     *
     * @param Product $product
     * @param LabelInterface $label
     * @return string|null
     */
    public function calculate(Product $product, LabelInterface $label): ?string
    {
        $storeId = (int) $product->getStoreId();
        [$price, $finalPrice] = $this->getPrices($product);

        if ($price <= 0 || $finalPrice >= $price) {
            return null;
        }

        $discountPercent = (int) round((($price - $finalPrice) / $price) * 100);
        $discountAmount = $price - $finalPrice;

        if ($label->getDiscountDisplay() === LabelInterface::DISCOUNT_AMOUNT) {
            return '-' . $this->priceCurrency->format(
                $discountAmount,
                false,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                $storeId ?: null
            );
        }

        return '-' . $discountPercent . '%';
    }

    /**
     * Resolve regular and final prices for discount labels.
     *
     * @param Product $product
     * @return array{0: float, 1: float}
     */
    private function getPrices(Product $product): array
    {
        try {
            $priceInfo = $product->getPriceInfo();
            $price = (float) $priceInfo->getPrice('regular_price')->getValue();
            $finalPrice = (float) $priceInfo->getPrice('final_price')->getValue();

            if ($price > 0) {
                return [$price, $finalPrice];
            }
        } catch (\Throwable $e) {
            // Fall back to legacy price accessors below.
        }

        return [
            (float) $product->getPrice(),
            (float) $product->getFinalPrice(),
        ];
    }
}
