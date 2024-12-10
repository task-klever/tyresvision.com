<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\OrderEditor\Controller\Adminhtml\Edit;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use MageWorx\OrderEditor\Api\OrderRepositoryInterface;
use MageWorx\OrderEditor\Api\QuoteRepositoryInterface;
use MageWorx\OrderEditor\Controller\Adminhtml\AbstractAction;
use MageWorx\OrderEditor\Helper\Data;
use MageWorx\OrderEditor\Model\Address as AddressModel;
use MageWorx\OrderEditor\Model\InventoryDetectionStatusManager;
use MageWorx\OrderEditor\Model\MsiStatusManager;
use MageWorx\OrderEditor\Model\Payment as PaymentModel;
use MageWorx\OrderEditor\Model\Shipping as ShippingModel;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;

/**
 * Class Address
 */
class Address extends AbstractAction
{
    const ADMIN_RESOURCE = 'MageWorx_OrderEditor::edit_address';

    /**
     * @var \MageWorx\OrderEditor\Model\Address
     */
    protected $address;

    /**
     * Address constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param RawFactory $resultRawFactory
     * @param Data $helper
     * @param ScopeConfigInterface $scopeConfig
     * @param QuoteRepositoryInterface $quoteRepository
     * @param ShippingModel $shipping
     * @param PaymentModel $payment
     * @param OrderRepositoryInterface $orderRepository
     * @param MsiStatusManager $msiStatusManager
     * @param InventoryDetectionStatusManager $inventoryDetectionStatusManager
     * @param SerializerJson $serializer
     * @param AddressModel $address
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        RawFactory $resultRawFactory,
        Data $helper,
        ScopeConfigInterface $scopeConfig,
        QuoteRepositoryInterface $quoteRepository,
        ShippingModel $shipping,
        PaymentModel $payment,
        OrderRepositoryInterface $orderRepository,
        MsiStatusManager $msiStatusManager,
        InventoryDetectionStatusManager $inventoryDetectionStatusManager,
        SerializerJson $serializer,
        AddressModel $address
    ) {
        parent::__construct(
            $context,
            $resultPageFactory,
            $resultRawFactory,
            $helper,
            $scopeConfig,
            $quoteRepository,
            $shipping,
            $payment,
            $orderRepository,
            $msiStatusManager,
            $inventoryDetectionStatusManager,
            $serializer
        );
        $this->address = $address;
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function update()
    {
        $addressId   = $this->getAddressId();
        $addressData = $this->getAddressData();

        $this->address->loadAddress($addressId);
        $this->address->updateAddress($addressData);
    }

    /**
     * @return string
     */
    protected function prepareResponse(): string
    {
        return static::ACTION_RELOAD_PAGE;
    }

    /**
     * @return int|null
     * @throws LocalizedException
     */
    protected function getAddressId()
    {
        $id = (int)$this->getRequest()->getParam('address_id', 0);
        if (empty($id)) {
            throw new LocalizedException(__('Empty param address_id'));
        }

        return $id ?: null;
    }

    /**
     * @return array|null
     * @throws LocalizedException
     */
    protected function getAddressData()
    {
        $data = $this->getRequest()->getParams();

        if (isset($data['billing_address'])) {
            return $data['billing_address'];
        }

        if (isset($data['shipping_address'])) {
            return $data['shipping_address'];
        }

        throw new LocalizedException(__('Have not address data information'));
    }
}
