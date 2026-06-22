<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Block\Adminhtml\Label\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Delete button for product label edit form.
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get button data.
     *
     * @return array
     */
    public function getButtonData(): array
    {
        $data = [];
        if ($this->getLabelId()) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __('Are you sure you want to delete this product label?') . '\', \''
                    . $this->getUrl('*/*/delete', ['label_id' => $this->getLabelId()]) . '\')',
                'sort_order' => 20,
            ];
        }
        return $data;
    }
}
