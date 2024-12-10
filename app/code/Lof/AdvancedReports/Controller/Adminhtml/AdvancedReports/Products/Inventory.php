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
namespace Lof\AdvancedReports\Controller\Adminhtml\AdvancedReports\Products; 
class Inventory extends \Lof\AdvancedReports\Controller\Adminhtml\AdvancedReports\Order
{
    /**
     * Shipping report action
     *
     * @return void
     */
    public function execute()
    {    
        //$this->_showLastExecutionTime(Flag::REPORT_SHIPPING_FLAG_CODE, 'earning');

        $this->_initAction()->_setActiveMenu(
            'Lof_AdvancedReports::inventory'
        )->_addBreadcrumb(
            __('Product - Inventory Reports'),
            __('Product - Inventory Reports')
        );
        $this->_registry->register('report_type', "inventory"); 
        $this->_registry->register('header_text', __("Product - Inventory Reports"));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Product - Inventory Reports'));   
        
        $gridBlock = $this->_view->getLayout()->getBlock('adminhtml_advancedreport_products_inventory.grid');  
        $filterFormBlock = $this->_view->getLayout()->getBlock('grid.filter.form');
        $this->_initReportAction([$gridBlock, $filterFormBlock], 'inventory'); 
        $this->_view->renderLayout();
    } 
 
       /**
     * Report action init operations
     *
     * @param array|Varien_Object $blocks
     * @return Mage_Adminhtml_Controller_Report_Abstract
     */
    public function _initReportAction($blocks, $report_type = "")
    {
        if (!is_array($blocks)) {
            $blocks = array($blocks);
        }
        $order = array();
        $sort_by = $this->getRequest()->getParam('sort');
        $dir = $this->getRequest()->getParam('dir');

        $requestData = $this->_objectManager->get(
            'Magento\Backend\Helper\Data'
        )->prepareFilterString(
            $this->getRequest()->getParam('loffilter')
        );   
        $requestData['store_ids'] = $this->getRequest()->getParam('store_ids');
        if(!isset($requestData['filter_from']) || !$requestData['filter_from']) {
          $requestData['filter_from'] = $this->getRequest()->getParam('filter_from');
        }
        if(!isset($requestData['filter_to']) || !$requestData['filter_to']) {
          $requestData['filter_to'] = $this->getRequest()->getParam('filter_to');
        }
        if(!$requestData['filter_from'] && !$requestData['filter_to']) {
            $cur_month = date("m");
            $cur_year = date("Y");
            $requestData['filter_from'] = $cur_month."/01/".$cur_year;
            $requestData['filter_to'] = date("m/d/Y");
        }
        if(!isset($requestData['product_sku']) || !$requestData['product_sku']) {
          $requestData['product_sku'] = $this->getRequest()->getParam('product_sku');
        }
        if(!isset($requestData['group_by']) || !$requestData['group_by']) {
          $requestData['group_by'] = $this->getRequest()->getParam('group_by');
        }
        if(!isset($requestData['limit']) || !$requestData['limit']) {
          $requestData['limit'] = $this->getRequest()->getParam('limit');
        } else {
          $requestData['limit'] = 20;
        }
        if(!isset($requestData['show_actual_columns']) || !$requestData['show_actual_columns']) {
          $requestData['show_actual_columns'] = $this->getRequest()->getParam('show_actual_columns');
        }
        if(!isset($requestData['name']) || !$requestData['name']) {
          $requestData['name'] = $this->getRequest()->getParam('name');
        }
        if(!isset($requestData['sku']) || !$requestData['sku']) {
          $requestData['sku'] = $this->getRequest()->getParam('sku');
        }
        if(!isset($requestData['order_statuses'])) {
            $requestData['order_statuses'] =  'complete';
        }
        if(!isset($requestData['show_order_statuses']) || (isset($requestData['show_order_statuses']) && $requestData['show_order_statuses'] == 0)) {
            $requestData['order_statuses'] = "";
        }

        if(!$requestData['group_by']){
          $requestData['group_by'] = "month";
        }
        
        $params = new \Magento\Framework\DataObject();
        // $params = new Varien_Object();

        foreach ($requestData as $key => $value) {
            if (!empty($value)) {
                $params->setData($key, $value);
            }
        }  

        $period_type = $this->_getPeriodType($requestData, $report_type);
        
        foreach ($blocks as $block) {
            if ($block) {
                $block->setReportType($report_type);
                $block->setPeriodType($period_type);
                $block->setFilterData($params);
                $block->setCulumnOrder($sort_by);
                $block->setOrderDir($dir);
            }
        }

        return $this;
    }


    public function decodeFilter(&$value)
    {
        $value = trim(rawurldecode($value));
    }

    
    protected function _getPeriodType ($requestData = array(), $report_type = "") {
        $period_type = (isset($requestData['group_by']) && $requestData['group_by'])?$requestData['group_by']:"";
        if($report_type) {
            switch ($report_type) {
              case 'products':
                $period_type = "main_table.name";
                break;
              
              default:
                # code...
                break;
            }
        }
        
        return $period_type;
    }

     /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        $this->initVerification();
        if (!$this->getData('is_valid') && !$this->getData('local_valid')) {
            return false;
        }
        return $this->_authorization->isAllowed('Lof_AdvancedReports::inventory');
    } 
    

}
