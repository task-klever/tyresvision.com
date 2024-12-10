<?php

namespace Hdweb\Purchaseorder\Block\Adminhtml;


class Create extends \Magento\Backend\Block\Widget\Container
{
   
  protected $purchaseorder;
  protected $purchaseorderitem;
  protected $orderRepository;
  protected $povendor;
  protected $storeManagerInterface;
   protected $scopeConfig;
   protected $_productRepository;
   protected $orderInterfaceFactory;
   protected $orderItemFactory;

 
  public function __construct(
    \Magento\Backend\Block\Widget\Context $context,
    \Hdweb\Purchaseorder\Model\Purchaseorder $purchaseorder,
    \Hdweb\Purchaseorder\Model\Purchaseorderitem $purchaseorderitem,
    \Magento\Sales\Api\Data\OrderInterface $orderRepository,
    \Hdweb\Purchaseorder\Model\Povendor $povendor,
    \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    \Magento\Catalog\Model\ProductRepository $productRepository,
    \Magento\Sales\Api\Data\OrderInterfaceFactory $orderInterfaceFactory,
    \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
    array $data = [])
    {
         parent::__construct($context, $data);
        $this->purchaseorder=$purchaseorder;
        $this->purchaseorderitem=$purchaseorderitem;
        $this->orderRepository=$orderRepository;
        $this->povendor=$povendor;
        $this->storeManagerInterface=$storeManagerInterface;
        $this->scopeConfig = $scopeConfig;
        $this->_productRepository = $productRepository;
        $this->orderInterfaceFactory = $orderInterfaceFactory;
        $this->orderItemFactory = $orderItemFactory;
    }
  

   public function getPono(){
          $collection =$this->purchaseorder->getCollection();

          if(count($collection) > 0 ){
                 $porefno=$collection->getLastItem()->getPoreferenceNo();
                 $porefno=str_replace('T','', $porefno);
                 $number = $porefno; // the number to format

                 return 'T'.str_pad(intval($number) + 1, strlen($number), '0', STR_PAD_LEFT);

          }else{
               
               return 'T000001';
          }

   }

   public function getOrder(){
           $order_id=$this->_request->getParam('order_id');
          
           $order_obj=$this->orderRepository->load($order_id);

           return $order_obj;
          
   }

    public function getVendor(){
           $collection =$this->povendor->getCollection()->setOrder('name','ASC');;
          
           return $collection;
          
   }

    public function getCurrencycode(){
           $code =$this->storeManagerInterface->getStore()->getCurrentCurrencyCode();
          
           return $code;
   }

   public function getvat(){
           $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
           $vat=$this->scopeConfig->getValue('productsearch/general/vet_percent_id', $storeScope);
          
           return $vat;
   }

    public function getNamebySku($sku){
           $product=$this->_productRepository->get($sku);
           return $product->getName();
   }

     public function getNamebyOrderitem($orderreference_no,$sku){
    
           $order=$this->orderInterfaceFactory->create()->loadByIncrementId($orderreference_no);
           $items=$this->orderItemFactory->create()->getCollection()->addFieldToFilter('order_id',array('eq'=>$order->getEntityId()))->addFieldToFilter('sku',array('eq'=>$sku))->getFirstItem();

           return $items->getName();
           
   }

   public function getPobyid(){

          $po_id=$this->_request->getParam('po_id');

          $collection =$this->purchaseorder->getCollection()->addFieldToFilter('main_table.id',$po_id);
          $data=array();
          if(count($collection) > 0 ){
                   $podetail=$collection->getLastItem();
                   $data['podetail']=$podetail->getData();
                   
                   $poitem =$this->purchaseorderitem->getCollection()->addFieldToFilter('poid',$po_id);
                   $data['poitem']=$poitem->getData();

                    return $data;

          }
   }
  
  

      

   // protected function _construct()
   //  {
   //      $this->_objectId = 'order_id';
   //      $this->_controller = 'order';
   //      $this->_mode = 'create';

   //      parent::_construct();

   //      $this->setId('sales_order_create');



   //      $this->buttonList->update('save', 'label', __('Submit Order'));
   //      $this->buttonList->update('save', 'onclick', 'order.submit()');
   //      $this->buttonList->update('save', 'class', 'primary');
   //      // Temporary solution, unset button widget. Will have to wait till jQuery migration is complete
   //      $this->buttonList->update('save', 'data_attribute', []);

   //      $this->buttonList->update('save', 'id', 'submit_order_top_button');
   //      // if ($customerId === null || !$storeId) {
   //      //     $this->buttonList->update('save', 'style', 'display:none');
   //      // }

   //      // $this->buttonList->update('back', 'id', 'back_order_top_button');
   //      // $this->buttonList->update('back', 'onclick', 'setLocation(\'' . $this->getBackUrl() . '\')');

   //      // $this->buttonList->update('reset', 'id', 'reset_order_top_button');

   //      // if ($customerId === null) {
   //      //     $this->buttonList->update('reset', 'style', 'display:none');
   //      // } else {
   //      //     $this->buttonList->update('back', 'style', 'display:none');
   //      // }

   //      // $confirm = __('Are you sure you want to cancel this order?');
   //      // $this->buttonList->update('reset', 'label', __('Cancel'));
   //      // $this->buttonList->update('reset', 'class', 'cancel');
   //      // $this->buttonList->update(
   //      //     'reset',
   //      //     'onclick',
   //      //     'deleteConfirm(\'' . $confirm . '\', \'' . $this->getCancelUrl() . '\')'
   //      // );
   //  }

   // protected function _prepareLayout()
   //  {

        
   //      // $addButtonProps = [
   //      //     'id' => 'add_new',
   //      //     'label' => __('Add New'),
   //      //     'class' => 'add',
   //      //     'button_class' => '',
   //      //     'class_name' => 'Magento\Backend\Block\Widget\Button\SplitButton',
   //      //     'options' => $this->_getAddButtonOptions(),
   //      // ];
   //      // $this->buttonList->add('add_new', $addButtonProps);

   //      $this->buttonList->update('save', 'label', __('Submit Order'));
   //      $this->buttonList->update('save', 'onclick', 'order.submit()');
   //      $this->buttonList->update('save', 'class', 'primary');
   //      // Temporary solution, unset button widget. Will have to wait till jQuery migration is complete
   //      $this->buttonList->update('save', 'data_attribute', []);

   //      $this->buttonList->update('save', 'id', 'submit_order_top_button');

        

   //      // $this->setChild(
   //      //     'grid',
   //      //     $this->getLayout()->createBlock('Hdweb\Installerservice\Block\Adminhtml\Service\Grid', 'brainvire.service.grid')
   //      // );
   //      return parent::_prepareLayout();
   //  }

    
    // protected function _getAddButtonOptions()
    // {

    //     $splitButtonOptions[] = [
    //         'label' => __('Add New'),
    //         'onclick' => "setLocation('" . $this->_getCreateUrl() . "')"
    //     ];

    //     return $splitButtonOptions;
    // }

    
    // protected function _getCreateUrl()
    // {
    //     return $this->getUrl(
    //         'installerservice/*/new'
    //     );
    // }

    
    // public function getGridHtml()
    // {
    //     return $this->getChildHtml('grid');
    // }

    
    public function getSaveUrl()
    {
        
            $url = $this->getUrl('purchaseorder/create/save');
        
           return $url;
    }
    public function getEditUrl()
    {
        
            $url = $this->getUrl('purchaseorder/create/editsave');
        
           return $url;
    }

    
    // public function getBackUrl()
    // {
    //     return $this->getUrl('sales/' . $this->_controller . '/');
    // }

}
