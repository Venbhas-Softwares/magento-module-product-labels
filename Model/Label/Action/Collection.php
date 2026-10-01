<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Model\Label\Action;

use Magento\Rule\Model\Action\Collection as CoreCollection;

/**
 * Unused actions collection required by rule abstract model.
 */
class Collection extends CoreCollection
{
    /**
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Rule\Model\ActionFactory $actionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Rule\Model\ActionFactory $actionFactory,
        array $data = []
    ) {
        parent::__construct($assetRepo, $layout, $actionFactory, $data);
        $this->setType(self::class);
    }
}
