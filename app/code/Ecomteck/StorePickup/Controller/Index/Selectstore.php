<?php

namespace Ecomteck\StorePickup\Controller\Index;

use Magento\Framework\View\Result\PageFactory;

class Selectstore extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;
    protected $request;
    protected $_checkoutSession;
    protected $messageManager;
    protected $store;

    public function __construct(
        \Magento\Framework\App\Request\Http $request, \Magento\Framework\App\Action\Context $context, PageFactory $resultPageFactory,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Ecomteck\StoreLocator\Model\StoresFactory $store
    ) {
        $this->request           = $request;
        $this->resultPageFactory = $resultPageFactory;
        $this->_checkoutSession  = $_checkoutSession;
        $this->messageManager    = $context->getMessageManager();
        $this->store             = $store;
        parent::__construct($context);
    }

    public function execute()
    {
        $post_Param = $this->getRequest()->getParams();
        if (isset($post_Param['pickup_store']) && isset($post_Param['pickup_date']) && isset($post_Param['pickup_time'])) {
            $storeid = $post_Param['pickup_store'];

            $pickup_store = $post_Param['pickup_store'];
            $pickup_date  = $post_Param['pickup_date'];
            $pickup_time  = $post_Param['pickup_time'];

            $quote = $this->_checkoutSession->getQuote();
            $quote->setPickupDate($pickup_date);
            $quote->setPickupTime($pickup_time);
            $quote->setPickupStore($pickup_store);

            $quote->setDeliveryDate($pickup_date);
            $quote->setDeliveryComment($pickup_time);

            $quote->save();

            $this->_checkoutSession->setPickupdate($pickup_date);
            $this->_checkoutSession->setPickuptime($pickup_time);
            $this->_checkoutSession->setPickupstoreid($pickup_store);
           // $this->messageManager->addSuccess('Date & Time selected');

            $this->_redirect('checkout');
        } else {

            $this->_redirect('storepickup');
        }
    }

}
