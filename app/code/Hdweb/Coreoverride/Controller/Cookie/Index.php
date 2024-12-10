<?php

namespace Hdweb\Coreoverride\Controller\Cookie;

class Index extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
	protected $_storeManager;
    protected $_configWriter;
    protected $_scopeConfig;
    protected $_cacheTypeList;
    protected $_cacheFrontendPool;
    protected $manStore;
    const COOKIE_NAME = 'test1';
    const COOKIE_DURATION = 86400; // lifetime in seconds
    /**
    * @var \Magento\Framework\Stdlib\CookieManagerInterface
    */
    protected $_cookieManager;
    /**
    * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
    */
    protected $_cookieMetadataFactory;
    protected $customerSession;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
       // \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Store\Model\StoreManagerInterface $manStore,
         \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
         \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
          \Magento\Customer\Model\Session $customerSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_cookieManager = $cookieManager;
        $this->_cookieMetadataFactory = $cookieMetadataFactory;
        $this->manStore = $manStore;
        $this->customerSession = $customerSession;
        parent::__construct($context);
        
    }

    public function execute()
    {

       // $metadata = $this->_cookieMetadataFactory
       //   ->createPublicCookieMetadata()
       //   ->setDuration(self::COOKIE_DURATION);
       //   $this->_cookieManager->setPublicCookie(
       //       self::COOKIE_NAME,
       //       'YOUR COOKIE VALUE',
       //       $metadata
       //   );
        $this->customerSession->setOfflineMethod(true);
    
        $resultRedirect = $this->resultRedirectFactory->create();
        $route = 'customer/account'; // w/o leading '/'
        $store = $this->manStore->getStore();
        $redirectUrl= $store->getBaseUrl();
        $this->messageManager->addSuccess(__('Offline payment method is available'));
       // $url = $store->getUrl($route); // second arg can be omitted 
        $resultRedirect->setUrl($redirectUrl);
        return $resultRedirect;
        }
        
        protected function _isAllowed()
        {
            return true;
        }
}