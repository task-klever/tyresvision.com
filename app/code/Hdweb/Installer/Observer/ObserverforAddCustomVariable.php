<?php
namespace Hdweb\Installer\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;

class ObserverforAddCustomVariable implements ObserverInterface
{
    protected $orderModel;
    protected $countryModel;
    protected $pickupstores;
    protected $country;
    protected $groupRepository;

    public function __construct(
        \Magento\Sales\Model\Order $orderModel,
        \Magento\Directory\Model\Country $countryModel,
        \Ecomteck\StoreLocator\Model\Stores $pickupstores,
        \Magento\Directory\Model\Country $country,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
    ){
        $this->orderModel = $orderModel;
        $this->countryModel = $countryModel;
        $this->pickupstores    = $pickupstores;
        $this->country    = $country;
        $this->groupRepository = $groupRepository;
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\App\Action\Action $controller */
        $transport = $observer->getTransport();
        $order_id =  $transport['order']->getId();
        $order = $this->orderModel->load($order_id);

        $customerGroupId = $order->getCustomerGroupId();
        $customerGroup = $this->groupRepository->getById($customerGroupId);
        $customerGroupCode = $customerGroup->getCode();
        $transport->setVar('b2b_order', '');
        if($customerGroupCode == 'Wholesale'){
            $transport['wholesale_customer'] = true;
            $transport['b2b_order'] = '(B2B)';
            $transport->setVar('b2b_order', '(B2B)');
        }
        
		
    }
}