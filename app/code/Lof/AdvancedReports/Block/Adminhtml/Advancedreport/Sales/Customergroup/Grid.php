<?php
/**
 * Landofcoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Landofcoder
 * @package    Lof_AdvancedReports
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\AdvancedReports\Block\Adminhtml\Advancedreport\Sales\Customergroup;

class Grid extends \Lof\AdvancedReports\Block\Adminhtml\Grid\AbstractGrid
{

    protected $_columnDate = 'main_table.created_at';
    protected $_columnGroupBy = '';
    protected $_defaultSort = 'period';
    protected $_defaultDir = 'ASC';
    protected $_resource_grid_collection = null;
    protected $_scopeconfig;
    public function _construct()
    {  
        parent::_construct(); 
        $this->setCountTotals(true);
        $this->setFilterVisibility(true);
        $this->setPagerVisibility(true);
        $this->setId('productsreportGrid');
        $this->setUseAjax(false);  
    }
 /**
     * {@inheritdoc}
     */
    public function getResourceCollectionName()
    {
        return 'Lof\AdvancedReports\Model\ResourceModel\Sales\Collection';
    }
    protected function _prepareColumns()
    {      
        $filterData = $this->getFilterData();

        $this->addColumn('group_name', array(
            'header' => __('Customer Group'),
            'index' => 'group_name',
            'width' => '100px',
            'totals_label'  => __('Total'),
            'html_decorators' => array('nobr'),
            'filter'    => false,
        ));

        $this->addColumn('total_qty_invoiced', array(
            'header'    => __('Quantity'),
            'index'     => 'total_qty_invoiced',
            'type'      => 'number',
            'total'     => 'sum',
            'filter'    => false,
        ));

        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }
        $currencyCode = $this->getCurrentCurrencyCode();
        $rate = $this->getRate($currencyCode);

        $this->addColumn('total_income_amount', array(
            'header'    => __('Profit'),
            'index'     => 'total_income_amount',
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'total'     => 'sum',
            'rate'          => $rate,
            'filter'    => false,
        ));
       
        $this->addExportType('*/*/exportSalesCustomergroupCsv', __('CSV'));
        $this->addExportType('*/*/exportSalesCustomergroupExcel', __('Excel XML')); 

        return parent::_prepareColumns();
    }

    protected function _prepareCollection()
    {

        $filterData = $this->getFilterData();
        $report_type = $this->getReportType(); 
        $limit = $filterData->getData("limit", null);
        if(!$limit) {
            $limit = $this->_defaultLimit;
        } 
        $report_field = $filterData->getData("report_field", null);
        $report_field = $report_field?$report_field: "main_table.created_at";
        $this->setCulumnDate($report_field);
        $this->setDefaultSort("total_qty_invoiced");
        $this->setDefaultDir("DESC");
        
        $storeIds = $this->_getStoreIds();  
        $resourceCollection = $this->_objectManager->create($this->getResourceCollectionName())
            ->prepareCustomergroupCollection() 
            ->setPeriodType($this->getPeriodType())
            ->setDateColumnFilter($this->_columnDate)
            ->addDateFromFilter($filterData->getData('filter_from', null))
            ->addDateToFilter($filterData->getData('filter_to', null)) 
            ->addStoreFilter($storeIds)
            ->setAggregatedColumns($this->_getAggregatedColumns());
 
            
        $this->_addOrderStatusFilter($resourceCollection, $filterData);
        $this->_addCustomFilter($resourceCollection, $filterData);
        $resourceCollection->getSelect()
                            ->group('period')
                            ->order(new \Zend_Db_Expr($this->getColumnOrder()." ".$this->getColumnDir()));
        $resourceCollection->applyCustomFilter(); 
 
        $resourceCollection->setPageSize((int) $this->getParam($this->getVarNameLimit(), $limit));
        $resourceCollection->setCurPage((int) $this->getParam($this->getVarNamePage(), $this->_defaultPage));


        if ($this->getCountSubTotals()) {
            $this->getSubTotals();
        }

        if (!$this->getTotals()) {
            $totalsCollection =  $this->_objectManager->create($this->getResourceCollectionName())
            ->prepareCustomergroupCollection()
            ->setDateColumnFilter($this->_columnDate)
            ->setPeriodType($this->getPeriodType())
            ->addDateFromFilter($filterData->getData('filter_from', null))
            ->addDateToFilter($filterData->getData('filter_to', null))
            ->addStoreFilter($storeIds)
            ->setAggregatedColumns($this->_getAggregatedColumns())
            ->isTotals(true);

            $this->_addOrderStatusFilter($totalsCollection, $filterData);
            $this->_addCustomFilter($totalsCollection, $filterData);

            $totalsCollection->getSelect()
                            ->group('period')
                            ->order(new \Zend_Db_Expr($this->getColumnOrder()." ".$this->getColumnDir()));

            $totalsCollection->applyCustomFilter();

            foreach ($totalsCollection as $item) {
                $this->setTotals($item);
                break;
            }
        } 
        $this->setCollection($resourceCollection); 
        if(!$this->_registry->registry('report_collection')) {
            $this->_registry->register('report_collection', $resourceCollection);
        } 

         $this->_prepareTotals('total_qty_invoiced,total_income_amount'); 
        return parent::_prepareCollection();
    } 

}