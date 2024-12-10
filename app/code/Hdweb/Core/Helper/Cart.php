<?php
namespace Hdweb\Core\Helper;

use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;

class Cart extends \Magento\Framework\App\Helper\AbstractHelper
{
   
   
    protected $cart;

    protected $request;

    protected $session;

    protected $couponFactory;

    protected $orderCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;


    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

   /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $sessionManager;

     protected $ruleRepository;

     protected $messageManager;
     

    public function __construct(
       \Magento\Checkout\Model\Cart $cart,
       \Magento\Framework\App\Request\Http $request,
       \Magento\Checkout\Model\Session $session,
       \Magento\SalesRule\Model\CouponFactory $couponFactory,
       \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
       CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager,
        \Magento\SalesRule\Model\RuleRepository $ruleRepository,
         \Magento\Framework\Message\ManagerInterface $messageManager 
    ) {
        $this->cart              = $cart;
        $this->request              = $request;
        $this->session = $session;
        $this->couponFactory = $couponFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;
        $this->ruleRepository = $ruleRepository;
        $this->messageManager = $messageManager;
    }

    public function applyCoupon()
    {
      $auto_coupon_cancel=$this->cookieManager->getCookie('auto_coupon_cancel');
      if(isset($auto_coupon_cancel) && $auto_coupon_cancel != 1) {
		  
       $coupons= array('LIVE100','TEST15');
           $cart = $this->cart;
            if(isset($coupons[0])) {  
              $couponCode = $coupons[0];
              $coupon = $this->couponFactory->create();
              $coupon->load($couponCode, 'code');
      
              $couponData=$coupon->getData();
              $rule1 = $this->ruleRepository->getById($couponData['rule_id']);

              if ($couponData['usage_limit'] != $couponData['times_used'] && ($rule1->getIsActive())) {
                  $quote = $cart->getQuote()->setCouponCode($couponCode)->collectTotals()->save();  
              }else{
               if(isset($coupons[1])) {   
                $couponCode = $coupons[1];
                $coupon = $this->couponFactory->create();
                $coupon->load($couponCode, 'code');
                $couponData=$coupon->getData();
                $rule2 = $this->ruleRepository->getById($couponData['rule_id']);
                if ($couponData['usage_limit'] != $couponData['times_used'] && ($rule2->getIsActive()) ) {
                    $quote = $cart->getQuote()->setCouponCode($couponCode)->collectTotals()->save();  
                }else{
                 if(isset($coupons[2])) {  
                  $couponCode = $coupons[2];
                  $coupon = $this->couponFactory->create();
                  $coupon->load($couponCode, 'code');
                  $couponData=$coupon->getData();
                  $rule3 = $this->ruleRepository->getById($couponData['rule_id']);

                  if ($couponData['usage_limit'] != $couponData['times_used'] && ($rule3->getIsActive()) ) {
                      $quote = $cart->getQuote()->setCouponCode($couponCode)->collectTotals()->save();  
                   }

                  }
				  if(isset($coupons[3])) {  
					  $couponCode = $coupons[3];
					  $coupon = $this->couponFactory->create();
					  $coupon->load($couponCode, 'code');
					  $couponData=$coupon->getData();
					  $rule3 = $this->ruleRepository->getById($couponData['rule_id']);

					  if ($couponData['usage_limit'] != $couponData['times_used'] && ($rule3->getIsActive()) ) {
						  $quote = $cart->getQuote()->setCouponCode($couponCode)->collectTotals()->save();  
					   }

					  }
                }
            }
           }
        } 
      }else{
		$coupons= array('TEST15');
        $cart = $this->cart;
		if(isset($coupons[0])){
			$couponCode = $coupons[0];
			$coupon = $this->couponFactory->create();
			$coupon->load($couponCode, 'code');
			$couponData=$coupon->getData();
			$rule2 = $this->ruleRepository->getById($couponData['rule_id']);
			if ($couponData['usage_limit'] != $couponData['times_used'] && ($rule2->getIsActive()) ) {
				$quote = $cart->getQuote()->setCouponCode($couponCode)->collectTotals()->save();
			}
		}
	  }
    }
}