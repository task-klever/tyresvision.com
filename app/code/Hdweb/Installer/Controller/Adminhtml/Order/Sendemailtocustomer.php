<?php

namespace Hdweb\Installer\Controller\Adminhtml\Order;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\ScopeInterface;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;


class Sendemailtocustomer extends \Magento\Backend\App\Action
{
   
  
    const NOTIFY_CUSTOMER_TEMPLATE  = 'installer/general/admin_notify_customer_email_template';

 
    protected $_order;
    protected $scopeConfig;
    protected $pickupstores;
    protected $transportBuilder;
    protected $stateInterface;
    protected $storeManagerInterface;
    protected $country;
    protected $addressRenderer;
    protected $paymentHelper;
    protected $identityContainer;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Ecomteck\StoreLocator\Model\Stores $pickupstores,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $stateInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Directory\Model\Country $country,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        OrderIdentity $identityContainer

    ) {
        parent::__construct($context);
        $this->_order          = $order;
        $this->scopeConfig     = $scopeConfig;
        $this->pickupstores    = $pickupstores;
        $this->transportBuilder          = $transportBuilder;
        $this->stateInterface     = $stateInterface;
        $this->storeManagerInterface    = $storeManagerInterface;
        $this->country    = $country;
        $this->addressRenderer = $addressRenderer;
        $this->paymentHelper = $paymentHelper;
        $this->identityContainer = $identityContainer;
    }
    public function execute()
    {

         $order_id = $this->getRequest()->getParam('order_id');

        $admin_installer_date = $this->getRequest()->getParam('installer_date');

        $admin_installer_comment = $this->getRequest()->getParam('installer_comment');

        $order = $this->_order->load($order_id);
        
        $installer_id=$order->getPickupStore();

        if(isset($installer_id) && !empty($installer_id) ) {

         $pickupstoresData=$this->pickupstores->load($installer_id);
         $installer_email=$order->getCustomerEmail();
        
            if (!empty($installer_email) && strpos($installer_email, '@') !== false) {
                $_transportBuilder = $this->transportBuilder;
                $inlineTranslation = $this->stateInterface;
                $storeManager      = $this->storeManagerInterface;
                $storeManager->setCurrentStore($order->getStore()->getId());
                $country = $this->country->load($pickupstoresData->getCountry())->getName();
                
                $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeManager->getStore()->getId());

                $payment            = $order->getPayment();
                $method             = $payment->getMethodInstance();
                $paymentmethodTitle = $method->getTitle();
               
                $templateVars = [
                'order' => $order,
                'orderitem' => $order->getAllItems(),
                'billing' => $order->getBillingAddress(),
                'payment_html' => $paymentmethodTitle,//$this->getPaymentHtml($order),
                'store' => $order->getStore(),
                'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
                'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
                'created_at_formatted' => $order->getCreatedAtFormatted(2),
                'admin_installer_date' => $admin_installer_date,
                'admin_installer_comment' => $admin_installer_comment,
                'installer_name'          => $pickupstoresData->getName(),
                'installer_street'        => $pickupstoresData->getAddress(),
                'installer_city'          => $pickupstoresData->getCity(),
                'installer_region'        => $pickupstoresData->getRegion(),
                'installer_country'       => $country,
                'installer_managername'   => '',//$installer_detail['storemanager_name'],
                'installer_email'         => $pickupstoresData->getEmail(),
                'installer_phone'         => 'T: '.$pickupstoresData->getPhone(),     
                'order_data' => [
                    'customer_name' => $order->getCustomerName(),
                    'is_not_virtual' => $order->getIsNotVirtual(),
                    'email_customer_note' => $order->getEmailCustomerNote(),
                    'frontend_status_label' => $order->getFrontendStatusLabel()
                ]
            ];

                $email                       = $this->scopeConfig->getValue('trans_email/ident_support/email', ScopeInterface::SCOPE_STORE);
                $name                        = $this->scopeConfig->getValue('trans_email/ident_support/name', ScopeInterface::SCOPE_STORE);
                $copy_to             = $this->scopeConfig->getValue('sales_email/order/copy_to', ScopeInterface::SCOPE_STORE);
                $from                = array('email' => $email, 'name' => $name);
                $inlineTranslation->suspend();
                $receiveremail       = explode(',', $installer_email);
               
                $notifyInstallerTemplate=$this->scopeConfig->getValue(self::NOTIFY_CUSTOMER_TEMPLATE, ScopeInterface::SCOPE_STORE);

                $transport = $_transportBuilder->setTemplateIdentifier($notifyInstallerTemplate)
                        ->setTemplateOptions($templateOptions)
                        ->setTemplateVars($templateVars)
                        ->setFrom($from)
                        ->addTo($installer_email)
                        ->addCc($copy_to)
                        ->getTransport();

                $transport->sendMessage();
                $inlineTranslation->resume();

                $this->messageManager->addSuccess(__('Email has been sent.'));
                $order->addStatusHistoryComment('Notify - Customer - ' . $admin_installer_date . ' - ' . $admin_installer_comment);
                $order->save();
                $this->_redirect('sales/order/view', array('order_id' => $order_id));
            } else {
                $this->messageManager->addError(__('Please add customer email address.'));
                $this->_redirect('sales/order/view', array('order_id' => $order_id));
            }

       }else{
               $this->messageManager->addError(__('Customer not found for this order'));
                $this->_redirect('sales/order/view', array('order_id' => $order_id));
         
       }     

    }
    
    protected function getFormattedBillingAddress($order)
    {
        return $this->addressRenderer->format($order->getBillingAddress(), 'html');
    }

    protected function getFormattedShippingAddress($order)
    {
        return $order->getIsVirtual()
            ? null
            : $this->addressRenderer->format($order->getShippingAddress(), 'html');
    }

    protected function getPaymentHtml($order)
    {
        return $this->paymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $this->identityContainer->getStore()->getStoreId()
        );
    }
  

}