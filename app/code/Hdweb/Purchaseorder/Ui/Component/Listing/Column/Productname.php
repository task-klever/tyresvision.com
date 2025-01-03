<?php
namespace Hdweb\Purchaseorder\Ui\Component\Listing\Column;
 
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
 
class Productname extends Column
{
 
    protected $_orderRepository;
    protected $_searchCriteria;
    protected $purchaseorder;
	protected $purchaseorderitem;
	protected $orderRepository;
	protected $povendor;
	protected $_productRepository;
    protected $orderInterfaceFactory;
    protected $orderItemFactory;
 
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        SearchCriteriaBuilder $criteria,
        \Hdweb\Purchaseorder\Model\Purchaseorder $purchaseorder,
		\Hdweb\Purchaseorder\Model\Purchaseorderitem $purchaseorderitem,
		\Magento\Sales\Api\Data\OrderInterface $orderRepository,
		\Hdweb\Purchaseorder\Model\Povendor $povendor,
		\Magento\Catalog\Model\ProductRepository $productRepository,
		\Magento\Sales\Api\Data\OrderInterfaceFactory $orderInterfaceFactory,
		\Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        array $components = [], array $data = [])
    {
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria; 
		$this->purchaseorder=$purchaseorder;
        $this->purchaseorderitem=$purchaseorderitem;
        $this->orderRepository=$orderRepository;
        $this->povendor=$povendor;
		$this->_productRepository = $productRepository;
        $this->orderInterfaceFactory = $orderInterfaceFactory;
        $this->orderItemFactory = $orderItemFactory;
		parent::__construct($context, $uiComponentFactory, $components, $data);
    }
 
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
				$order	= $this->orderInterfaceFactory->create()->loadByIncrementId($item["orderreference_no"]);
				$data	= $this->orderItemFactory->create()->getCollection()->addFieldToFilter('order_id',array('eq'=>$order->getEntityId()))->addFieldToFilter('sku',array('eq'=>$item["sku"]))->getFirstItem();
                $item[$this->getData('name')] = $data->getName();
            }
        }
        return $dataSource;
    }
}