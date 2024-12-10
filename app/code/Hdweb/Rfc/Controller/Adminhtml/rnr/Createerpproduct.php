<?php
namespace Hdweb\Rfc\Controller\Adminhtml\rnr;

class Createerpproduct extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;
    protected $_scopeConfig;
    protected $_product;
    protected $_storeManager;
    protected $_dir;
    protected $userFactory;
    protected $_messageManager;
	const ERP_URL = 'https://api.businesscentral.dynamics.com/v2.0/671a2e1d-a893-4a6d-afbf-b73562b1a7f5/YallaUAT/WS/My%20Company/Codeunit/YallaWS';
	const USERNAME = 'APNT';
	const PASSWORD = 'GBcV5BR0LVU+j7p2oU8jBwGrqAgfmijEqCT4KOzYuxc=';

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->resultPageFactory           = $resultPageFactory;
        $this->_scopeConfig                = $scopeConfig;
        $this->_product                    = $product;
        $this->_storeManager               = $storeManager;
        $this->_dir                        = $dir;
        $this->userFactory                 = $userFactory;
        $this->_messageManager             = $messageManager;
    }
    public function execute()
    {
        $data           = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
		$order_id       = $this->getRequest()->getParam('order_id');
        $product_id       = $this->getRequest()->getParam('product_id');
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$createErpProduct =  $objectManager->create('Hdweb\Rfc\Helper\Data')->createErpProduct($product_id);
		$this->_redirect('sales/order/view', array('order_id' => $order_id));
        //echo $product_id;die;
	}
}