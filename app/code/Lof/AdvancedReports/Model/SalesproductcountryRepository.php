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
 * @copyright  Copyright (c) 2017 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\AdvancedReports\Model;
use Lof\AdvancedReports\Api\SalesproductcountryInterface;
use Lof\AdvancedReports\Model\AbstractReport;
use Magento\Framework\Api\SortOrder;
 
class SalesproductcountryRepository extends AbstractReport implements SalesproductcountryInterface
{
    protected $_limit = 10;
    /**
     * GROUP BY criteria
     *
     * @var string
     */
    protected $_columnDate = 'main_table.created_at';
     protected $_defaultSort = 'total_qty_invoiced';
    protected $_defaultDir = 'DESC';

    public function __construct(   
        \Lof\AdvancedReports\Helper\Data $helperData, 
        \Magento\Framework\ObjectManagerInterface $objectManager, 
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        \Lof\AdvancedReports\Helper\Api\Datefield $helperDatefield, 
        \Lof\AdvancedReports\Api\Data\SalesproductcountrydataInterfaceFactory $searchResultsFactory
        )
    {
        $this->searchResultsFactory = $searchResultsFactory;
        parent::__construct($helperData, $objectManager, $storeManager, $localeCurrency, $searchCriteriaBuilder, $localeLists, $helperDatefield);
    }
    /**
     * {@inheritdoc}
     */
    public function getResourceCollectionName()
    {
        return 'Lof\AdvancedReports\Model\ResourceModel\Sales\Collection';
    }

    /**
    * @return \Magento\Framework\DataObject
    */
    public function initFilterData($filter_field = []) {
        $requestData = [];
        $lofFilter = isset($filter_field['lofFilter'])?isset($filter_field['lofFilter']):null;
        $storeIds = isset($filter_field['storeIds'])?isset($filter_field['storeIds']):null;

        if($lofFilter) {
            $requestData = $this->_objectManager->get(
                'Magento\Backend\Helper\Data'
            )->prepareFilterString(
                $lofFilter
            );
        }

        $requestData['store_ids'] = $storeIds;

        if(!isset($requestData['report_field']) || !$requestData['report_field']) {
          $requestData['report_field'] = isset($filter_field['report_field'])?$filter_field['report_field']:$this->_columnDate;
        }
        if(!isset($requestData['filter_from']) || !$requestData['filter_from']) {
          $requestData['filter_from'] = isset($filter_field['filter_from'])?$filter_field['filter_from']:"";
        }
        if(!isset($requestData['filter_to']) || !$requestData['filter_to']) {
          $requestData['filter_to'] = isset($filter_field['filter_to'])?$filter_field['filter_to']:"";
        }
        
        if(!isset($requestData['group_by']) || !$requestData['group_by']) {
          $requestData['group_by'] = isset($filter_field['group_by'])?$filter_field['group_by']:"month";
        }
       
        if(!isset($requestData['show_order_statuses']) || ($requestData['show_order_statuses'] == NULL && $requestData['show_order_statuses'] == "")) {
          $requestData['show_order_statuses'] = isset($filter_field['show_order_statuses'])?(int)$filter_field['show_order_statuses']:1;;
        }

        if(!isset($requestData['order_statuses'])) {
            $requestData['order_statuses'] =  isset($filter_field['order_statuses'])?$filter_field['order_statuses']:"complete";
        }
        if($requestData['show_order_statuses'] == 0) {
            $requestData['order_statuses'] = "";
        }

        $params = new \Magento\Framework\DataObject();

        foreach ($requestData as $key => $value) {
            if (!empty($value)) {
                $params->setData($key, $value);
            }
        }
        $this->setFilterData($params);

        return $params;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Lof\AdvancedReports\Api\Data\SalesproductcountrydataInterface
     */
    public function getSalesProductCountry(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria) {
        $cur_month = date("m");
        $cur_year = date("Y");
        $filter_fields = [
                        "show_order_statuses"=>1,
                        "filter_from"=>$cur_month."/01/".$cur_year,
                        "filter_to"=>date("m/d/Y"),
                        "order_statuses"=>"complete",
                        "product_sku" => "",
                        "product_id" => "",
                        "group_by"=>"month",
                        "report_field"=>"main_table.created_at",
                        "lofFilter"=>"",
                        "storeIds"=>""];

        //Convert search criteria to specify filter params
        
        foreach ($searchCriteria->getFilterGroups() as $group) {
            if(!$group)
                continue;
            //var \Magento\Framework\Api\Search\FilterGroup $group
            foreach ($group->getFilters() as $filter) {
                $field = $filter->getField();
                $value = $filter->getValue();
                if($field != "filter_groups" && $field != "sort_orders" && isset($filter_fields[$field])) {
                    $filter_fields[$field] = $value;
                }
            }
        }

        //Init filter data to convert into a filter object of the report
        $this->initFilterData($filter_fields);

        $filterData = $this->getFilterData();
        $store_ids = $this->_getStoreIds();
  
        $resourceCollection = $this->_objectManager->create($this->getResourceCollectionName())
            ->prepareProductReportCollection()
            ->setMainTableId("product_id")
            ->setPeriodType($this->_getPeriodType())
            ->setDateColumnFilter($this->_columnDate)
            ->addDateFromFilter($filterData->getData('filter_from', null))
            ->addDateToFilter($filterData->getData('filter_to', null))  
            ->addProductIdFilter($filterData->getData('product_id', null))
            ->addProductSkuFilter($filterData->getData('product_sku', null))
            ->addStoreFilter($store_ids);

        $this->_addOrderStatusFilter($resourceCollection, $filterData);

        /** @var SortOrder $sortOrder */
        $set_order = false;
        if((array)$searchCriteria->getSortOrders()){
            foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
                $field = $sortOrder->getField();
                if($field) {
                    $set_order = true;
                    $resourceCollection->addOrder(
                        $field,
                        ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                    );
                }
            }
        }

        if(!$set_order) {
            $resourceCollection->getSelect() 
                            ->order(new \Zend_Db_Expr($this->_defaultSort." ".$this->_defaultDir));
        }

        if($currentPage = $searchCriteria->getCurrentPage()) {
            $resourceCollection->setCurPage($currentPage);
        }
        if($pageSize = $searchCriteria->getPageSize()) {
            $resourceCollection->setPageSize($pageSize);
        }

        $resourceCollection->applyCustomFilter();
        $resourceCollection->load();

        $this->_convertGridData($resourceCollection);
        //init \Lof\AdvancedReports\Api\Data\SalesbyproductdataInterface
        $searchResult = $this->searchResultsFactory->create();

        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($resourceCollection->getItems());
        $searchResult->setTotalCount($resourceCollection->getSize());
        return $searchResult;
    }

    protected function _getPeriodType () {
        $filterData = $this->getFilterData();
        return $filterData->getData("group_by");
    }

    protected function _convertGridData(&$collection = null) {

        if($collection && $collection->getSize()) {
            foreach($collection as &$item) {
                $total_revenue_currency = $this->formatCurrency($item->getTotalRevenueAmount());
                $item->setData("total_revenue_currency", $total_revenue_currency);
            }
        }
        return $collection;
    }
}