<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Block\Adminhtml\Sales\Order\Edit;

use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

/**
 * Class DeleteOrderButton
 */
class DeleteOrderButton extends Container
{
    const ADMIN_RESOURCE = 'MageWorx_OrderEditor::delete_order';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @param Context  $context
     * @param Registry $registry
     * @param array    $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Block constructor adds buttons
     *
     * @return void
     */
    protected function _construct()
    {
        if ($this->_authorization->isAllowed(static::ADMIN_RESOURCE)) {
            $this->addButton(
                'order_editor_delete_order',
                $this->getButtonData()
            );
        }
        parent::_construct();
    }

    /**
     * Return button attributes array
     */
    public function getButtonData(): array
    {
        $message = $this->escapeJs(__('Are you sure you want to completely delete selected order?'));

        return [
            'label'   => __('Delete'),
            'class'   => 'edit primary',
            'onclick' => 'confirmSetLocation(\'' . $message . '\', \'' . $this->getDeleteUrl() . '\')'
        ];
    }

    /**
     * @return string
     */
    private function getDeleteUrl(): string
    {
        return $this->getUrl('ordereditor/order/delete', ['order_id' => $this->getOrderId()]);
    }

    /**
     * @return int|null
     */
    private function getOrderId()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->coreRegistry->registry('current_order');
        if (!$order) {
            return null;
        }

        return (int)$order->getId();
    }
}
