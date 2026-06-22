<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Plugin\Block\Product;

use Magento\Catalog\Block\Product\Image;
use Magento\Catalog\Block\Product\ImageFactory;
use Magento\Catalog\Model\Product;

/**
 * Attach the product model to image blocks so label plugins can resolve rules.
 */
class ImageFactoryPlugin
{
    /**
     * Attach the product model to the image block after creation.
     *
     * @param ImageFactory $subject
     * @param Image $result
     * @param Product $product
     * @param string $imageId
     * @param array|null $attributes
     * @return Image
     */
    public function afterCreate(
        ImageFactory $subject,
        Image $result,
        Product $product,
        string $imageId,
        ?array $attributes = null
    ): Image {
        $result->setData('product', $product);
        return $result;
    }
}
