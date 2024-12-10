<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ecomteck\StorePickup\Controller\Index;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;

class Setfitment extends \Magento\Framework\App\Action\Action {

    protected $_checkoutSession;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    protected $_scopeConfig;
	protected $store;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    public function __construct(
    \Magento\Framework\App\Action\Context $context,
	\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
	\Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
	\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
	\Magento\Checkout\Model\Session $checkoutSession,
	\Ecomteck\StoreLocator\Model\StoresFactory $store
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->resultJsonFactory 	= $resultJsonFactory;
        $this->resultRawFactory 	= $resultRawFactory;
        $this->_checkoutSession 	= $checkoutSession;
		$this->store            	= $store;
    }

    public function execute() {
		$postData = $this->getRequest()->getPostValue();
		$isFitment = $postData['fitment_installer'];	
		$pickup_store = $postData['pickup_store'];
		$quote = $this->_checkoutSession->getQuote();
		$this->_checkoutSession->unsIsFitmentData();		
		$this->_checkoutSession->setIsFitmentData($isFitment);
		if($isFitment == 0 && $pickup_store != ''){
			$pickup_date = date('m/d/y', strtotime(' + 3 day'));
            $pickup_time  = '11:00am';
            $quote->setPickupDate($pickup_date);
            $quote->setPickupTime($pickup_time);
            $quote->setPickupStore($pickup_store);
            $quote->setDeliveryDate($pickup_date);
            $quote->setDeliveryComment($pickup_time);
            $quote->save();
            $this->_checkoutSession->setPickupdate($pickup_date);
            $this->_checkoutSession->setPickuptime($pickup_time);
            $this->_checkoutSession->setPickupstoreid($pickup_store);
		}else{
			$quote->setPickupDate('');
            $quote->setPickupTime('');
            $quote->setPickupStore('');
            $quote->setDeliveryDate('');
            $quote->setDeliveryComment('');
            $quote->save();
            $this->_checkoutSession->setPickupdate('');
            $this->_checkoutSession->setPickuptime('');
            $this->_checkoutSession->setPickupstoreid('');
		}		
    }
}