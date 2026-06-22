<?php
declare(strict_types=1);

namespace Venbhas\ProductLabels\Block\Adminhtml\Label\Edit;

use Magento\Backend\Block\Widget\Context;

/**
 * Generic button for product label edit form.
 */
class GenericButton
{
    /** @var Context */
    protected $context;

    /**
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Get label id from request.
     *
     * @return int|null
     */
    public function getLabelId(): ?int
    {
        return (int) $this->context->getRequest()->getParam('label_id') ?: null;
    }

    /**
     * Get URL for route.
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl(string $route = '', array $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
