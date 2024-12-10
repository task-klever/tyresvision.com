<?php

namespace Hdweb\Installer\Helper;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\UrlInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $messageManager;
    protected $_objectManager;
    protected $_resource;
    protected $_urlBuilder;
    protected $storeManager;
    protected $categoryData;
    protected $_categoryFactory;
    protected $_customerSession;
    protected $_shipconfig;
	protected $cart;
    protected $_productRepository;

    public function __construct(
    \Magento\Framework\App\Helper\Context $context, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Message\ManagerInterface $messageManager, \Magento\Framework\App\ResourceConnection $resource, \Magento\Catalog\Model\Category $categoryData, \Magento\Wishlist\Helper\Data $wishlistData, \Magento\Catalog\Model\CategoryFactory $categoryFactory, \Magento\Customer\Model\Session $customerSession, \Magento\Shipping\Model\Config $shipconfig,
        \Magento\User\Model\User $adminUser, \Magento\Checkout\Model\Cart $cart, \Magento\Catalog\Api\ProductRepositoryInterface $_productRepository
    ) {
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->storeManager = $storeManager;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->messageManager = $messageManager;
        $this->_resource = $resource;
        $this->categoryData = $categoryData;
        $this->wishlistData = $wishlistData;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_shipconfig = $shipconfig;
        $this->_categoryFactory = $categoryFactory;
        $this->_customerSession = $customerSession;
        $this->_adminUser = $adminUser;
		$this->cart = $cart;
        $this->_productRepository  = $_productRepository;
    }

   

      public function getManagerList(){
        $usermodel = $this->_adminUser->getCollection()->addFieldToFilter('is_active', 1)->addFieldToFilter('detail_role.role_name','CSE');
       // $email = $usermodel->getColumnValues('email');
         $user_id = $usermodel->getColumnValues('user_id');
        $fname = $usermodel->getColumnValues('username');
        $lname = $usermodel->getColumnValues('lastname');
        $userList = array_combine($user_id,$fname);//all api user

        return $userList;
    }

    public function checkIsFitment()
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $checkoutSession = $objectManager->get('\Magento\Checkout\Model\Session');
        $quote = $checkoutSession->getQuote();
        $items = $quote->getAllItems();
        foreach ($items as $key => $item) {
          $productId = $item->getProduct()->getId();
          
          $product = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);
          $isFitment = $product->getData('is_fitment');
          if ($isFitment == 0) {
            break;
          }
        }
        if ($isFitment) {
            return true;
        } else {
            return false;
        }
    }

}