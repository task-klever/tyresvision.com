<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Block\Adminhtml\Sales\Order\Edit;

use Magento\Backend\Block\Template;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;

class Wrapper extends Template
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var SerializerJson
     */
    protected $serializer;

    /**
     * @param Template\Context $context
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param SerializerJson $serializer
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\AuthorizationInterface $authorization,
        SerializerJson $serializer,
        array $data = []
    ) {
        $this->authorization = $authorization;
        $this->serializer    = $serializer;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getJsonParamsItems()
    {
        $data = [
            'loadFormUrl'       => $this->getUrl('ordereditor/form/load'),
            'updateUrl'         => $this->getUrl('ordereditor/edit/items'),
            'discardChangesUrl' => $this->getUrl('ordereditor/edit/restoreQuote'),
            'isAllowed'         => $this->authorization->isAllowed('MageWorx_OrderEditor::edit_items')
        ];

        return $this->serializer->serialize($data);
    }

    /**
     * @return string
     */
    public function getJsonParamsAddress()
    {
        $data = [
            'loadFormUrl' => $this->getUrl('ordereditor/form/load'),
            'updateUrl'   => $this->getUrl('ordereditor/edit/address'),
            'isAllowed'   => $this->authorization->isAllowed('MageWorx_OrderEditor::edit_address')
        ];

        return $this->serializer->serialize($data);
    }

    /**
     * @return string
     */
    public function getJsonParamsShipping()
    {
        $data = [
            'loadFormUrl' => $this->getUrl('ordereditor/form/load'),
            'updateUrl'   => $this->getUrl('ordereditor/edit/shipping'),
            'isAllowed'   => $this->authorization->isAllowed('MageWorx_OrderEditor::edit_shipping')
        ];

        return $this->serializer->serialize($data);
    }

    /**
     * @return string
     */
    public function getJsonParamsPayment()
    {
        $data = [
            'loadFormUrl' => $this->getUrl('ordereditor/form/load'),
            'updateUrl'   => $this->getUrl('ordereditor/edit/payment'),
            'isAllowed'   => $this->authorization->isAllowed('MageWorx_OrderEditor::edit_payment')
        ];

        return $this->serializer->serialize($data);
    }

    /**
     * @return string
     */
    public function getJsonParamsAccount()
    {
        $data = [
            'loadFormUrl'   => $this->getUrl('ordereditor/form/load'),
            'updateUrl'     => $this->getUrl('ordereditor/edit/account'),
            'renderGridUrl' => $this->getUrl('ordereditor/edit_account_widget/chooser'),
            'isAllowed'     => $this->authorization->isAllowed('MageWorx_OrderEditor::edit_account')
        ];

        return $this->serializer->serialize($data);
    }

    /**
     * @return string
     */
    public function getJsonParamsInfo()
    {
        $data = [
            'loadFormUrl' => $this->getUrl('ordereditor/form/load'),
            'updateUrl'   => $this->getUrl('ordereditor/edit/info'),
            'isAllowed'   => $this->authorization->isAllowed('MageWorx_OrderEditor::edit_info')
        ];

        return $this->serializer->serialize($data);
    }

    /**
     * @return bool
     */
    public function isEditAllowed(): bool
    {
        return $this->_authorization->isAllowed('MageWorx_OrderEditor::edit_order');
    }
}
