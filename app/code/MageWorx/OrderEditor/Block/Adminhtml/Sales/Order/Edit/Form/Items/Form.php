<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OrderEditor\Block\Adminhtml\Sales\Order\Edit\Form\Items;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Form extends \Magento\Sales\Block\Adminhtml\Order\Create\Form
{
    /**
     * @var \MageWorx\OrderEditor\Helper\Data
     */
    protected $helperData;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Customer\Model\Metadata\FormFactory $customerFormFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Customer\Model\Address\Mapper $addressMapper
     * @param \MageWorx\OrderEditor\Helper\Data $helperData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Customer\Model\Metadata\FormFactory $customerFormFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        \MageWorx\OrderEditor\Helper\Data $helperData,
        array $data = []
    ) {
        $this->helperData = $helperData;
        parent::__construct(
            $context,
            $sessionQuote,
            $orderCreate,
            $priceCurrency,
            $jsonEncoder,
            $customerFormFactory,
            $customerRepository,
            $localeCurrency,
            $addressMapper,
            $data
        );
    }

    /**
     * Retrieve quote model object
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->helperData->getQuote();
    }

    /**
     * Retrieve customer identifier
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->helperData->getCustomerId();
    }

    /**
     * Retrieve store model object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->helperData->getStore();
    }

    /**
     * Retrieve store identifier
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->helperData->getStoreId();
    }

    /**
     * Get order data jason
     *
     * @return string
     */
    public function getOrderDataJson()
    {
        $data = [];
        $this->_storeManager->setCurrentStore($this->getStoreId());
        if ($this->getCustomerId()) {
            $data['customer_id'] = $this->getCustomerId();
        }
        if ($this->getStoreId() !== null) {
            $data['store_id'] = $this->getStoreId();
            $currency = $this->_localeCurrency->getCurrency($this->getStore()->getCurrentCurrencyCode());
            $symbol = $currency->getSymbol() ? $currency->getSymbol() : $currency->getShortName();
            $data['currency_symbol'] = $symbol;
            $data['shipping_method_reseted'] = !(bool)$this->getQuote()->getShippingAddress()->getShippingMethod();
            $data['payment_method'] = $this->getQuote()->getPayment()->getMethod();
        }
        $data['quote_id'] = $this->_sessionQuote->getQuoteId();

        return $this->_jsonEncoder->encode($data);
    }
}
