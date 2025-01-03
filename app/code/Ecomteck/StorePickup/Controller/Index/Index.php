<?php

namespace Ecomteck\StorePickup\Controller\Index;

use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;
    protected $request;
    protected $resultRedirectFactory;
	protected $_checkoutSession;
	protected $_scopeConfig;
	protected $_hdWebIntallerHelper;
	protected $customerSession;
	protected $groupRepository;

    public function __construct(
        \Magento\Framework\App\Request\Http $request, \Magento\Framework\App\Action\Context $context, PageFactory $resultPageFactory, \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
		\Magento\Checkout\Model\Session $_checkoutSession,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Hdweb\Installer\Helper\Data $hdWebInstallerHelper,
		\Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
    ) {
        $this->request           = $request;
        $this->resultPageFactory = $resultPageFactory;
		$this->resultRedirectFactory = $resultRedirectFactory;
		$this->_checkoutSession  = $_checkoutSession;
		$this->_scopeConfig  = $scopeConfig;
		$this->_hdWebIntallerHelper  = $hdWebInstallerHelper;
		$this->customerSession = $customerSession;
        $this->groupRepository = $groupRepository;
        parent::__construct($context);
    }

    public function execute()
    {
		$quote = $this->_checkoutSession->getQuote();
		$pickup_store = $quote->getPickupStore();
		$noFitmentPickupStoreId = $this->_scopeConfig->getValue('carttostorepickup/general/no_fitment_installer', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if ($this->customerSession->isLoggedIn()){
            $customerGroupId = $this->customerSession->getCustomerGroupId();
            $customerGroup = $this->groupRepository->getById($customerGroupId);
            $customerGroupCode = $customerGroup->getCode();
            if($customerGroupCode == 'Wholesale'){
				$this->_checkoutSession->unsIsFitmentData();
				$this->_checkoutSession->setIsFitmentData(0);
				$pickup_date = date('m/d/y', strtotime(' + 3 day'));
				$pickup_time  = '11:00am';
				$quote->setPickupDate($pickup_date);
				$quote->setPickupTime($pickup_time);
				$quote->setPickupStore($noFitmentPickupStoreId);
				$quote->setDeliveryDate($pickup_date);
				$quote->setDeliveryComment($pickup_time);
				$quote->save();
				$this->_checkoutSession->setPickupdate($pickup_date);
				$this->_checkoutSession->setPickuptime($pickup_time);
				$this->_checkoutSession->setPickupstoreid($noFitmentPickupStoreId);
                $resultRedirect = $this->resultRedirectFactory->create();
				$resultRedirect->setPath('checkout');
				return $resultRedirect;
            }       
        }
		
		$noFitment = $this->_checkoutSession->getIsFitmentData();
		$isFitment = $this->_hdWebIntallerHelper->checkIsFitment();
		
		if($noFitmentPickupStoreId && ($pickup_store == $noFitmentPickupStoreId)){
			$resultRedirect = $this->resultRedirectFactory->create();
			$resultRedirect->setPath('checkout');
			return $resultRedirect;
		} else if(!$isFitment){
			$this->_checkoutSession->unsIsFitmentData();
			$this->_checkoutSession->setIsFitmentData(0);
			$pickup_date = date('m/d/y', strtotime(' + 3 day'));
			$pickup_time  = '11:00am';
			$quote->setPickupDate($pickup_date);
			$quote->setPickupTime($pickup_time);
			$quote->setPickupStore($noFitmentPickupStoreId);
			$quote->setDeliveryDate($pickup_date);
			$quote->setDeliveryComment($pickup_time);
			$quote->save();
			$this->_checkoutSession->setPickupdate($pickup_date);
			$this->_checkoutSession->setPickuptime($pickup_time);
			$this->_checkoutSession->setPickupstoreid($noFitmentPickupStoreId);

			$resultRedirect = $this->resultRedirectFactory->create();
			$resultRedirect->setPath('checkout');
			return $resultRedirect;

		}
		else{
			$this->_view->loadLayout();
			$resultPage = $this->resultPageFactory->create();
			return $resultPage;
			$this->_view->renderLayout();
		}
    }

}
