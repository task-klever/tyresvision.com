<?php 
namespace Hdweb\Purchaseorder\Plugin;

use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Grid\Collection as SalesOrderGridCollection;
use Magento\Framework\Registry;

class SalesInvoiceCustomColumn
{
    private $messageManager;
    private $collection;

    public function __construct(MessageManager $messageManager,
        SalesOrderGridCollection $collection,
        Registry $registry
    ) {

        $this->messageManager = $messageManager;
        $this->collection = $collection;
         $this->registry = $registry;
    }

    public function aroundGetReport(
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject,
        \Closure $proceed,
        $requestName
    ) {
    	
        $result = $proceed($requestName);
        if ($requestName == 'sales_order_invoice_grid_data_source') {
            if ($result instanceof $this->collection
            ) {
            	if (is_null($this->registry->registry('invoice_grid_joined'))) {

	                $select = $this->collection->getSelect(); // 'po.grandtotal as pograndtotal'
	                $select->joinLeft(
	                    ["po" => $this->collection->getTable("purchase_order")],
	                    'main_table.order_increment_id = po.orderreference_no',
                         array('pograndtotal' => new \Zend_Db_Expr('SUM(po.grandtotal)') 
                            ,'margin' => new \Zend_Db_Expr(
                              '(main_table.grand_total - SUM(po.grandtotal)) / main_table.grand_total * 100') )
	                );     
                  ///  $select->columns(array('pograndtotal' => 'SUM(po.grandtotal)'));
                  //  $select->columns(array('margin' => new \Zend_Db_Expr(
                           //   '(main_table.grand_total - SUM(po.grandtotal)) / main_table.grand_total * 100') ));
                   // $select->columns(['pograndtotal' => new \Zend_Db_Expr('SUM(total)')]) 
                    $select->group('main_table.order_increment_id');         
	                $this->registry->register('invoice_grid_joined', true);
	             }   
                return $this->collection;
            }
        }
        return $result;
    }
}