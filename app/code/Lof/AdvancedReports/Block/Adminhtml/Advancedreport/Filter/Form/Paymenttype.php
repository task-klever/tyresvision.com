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
namespace Lof\AdvancedReports\Block\Adminhtml\Advancedreport\Filter\Form;

/**
 * Sales Adminhtml report filter form order
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Paymenttype extends \Lof\AdvancedReports\Block\Adminhtml\Advancedreport\Filter\Form
{
    protected function _prepareForm()
    {
        $actionUrl = $this->getUrl($this->getFormActionUrl());
        $report_type = $this->getReportType();  
        $notshow_actual = array("productsnotsold"); 

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'filter_form',
                    'action' => $actionUrl,
                    'method' => 'get'
                ]
            ]
        );
        $htmlIdPrefix = 'products_report_';
        $form->setHtmlIdPrefix($htmlIdPrefix); 
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Filter')]);

        $statuses = $this->_objectManager->create('Magento\Sales\Model\Order\Config')->getStatuses(); 

       $values = array();
        foreach ($statuses as $code => $label) {
            if (false === strpos($code, 'pending')) {
                $values[] = array(
                    'label' => __($label),
                    'value' => $code
                );
            }
        }

        $fieldset->addField('store_ids', 'hidden', array(
            'name'  => 'store_ids'
        ));
        $fieldset->addField('filter_from', 'hidden', array(
            'name'  => 'filter_from'
        ));
        $fieldset->addField('filter_to', 'hidden', array(
            'name'  => 'filter_to'
        ));
        $fieldset->addField('report_field', 'select', array(
            'name'      => 'report_field',
            'options'   => array(
                        'main_table.created_at' =>  __('Order Created'),
                        'main_table.updated_at' =>  __('Order Updated'), 
            ),
            'label'     => __('Match Period To'),
        ));
        //Config date range
        $default_value = "May 25, 2016 - November 28, 2016";
        $filterData = $this->getFilterData();
        $filter_from = $filterData->getData("filter_from");
        $filter_to = $filterData->getData("filter_to");

        //Check if empty filter from and filter to, set default from date and to date
        if(!$filter_from && !$filter_to) {  
            if(in_array($report_type, $report_types)) {
                $cur_month = date("m");
                $cur_year = date("Y");
                $filter_from = $cur_month."/01/".$cur_year;
                $filter_to = date("m/d/Y"); 
            }
        }
        $filter_from_jstime = '';
        $filter_to_jstime = '';
        if($filter_from && $filter_to) {  
            $filter_from_jstime = strtotime($filter_from)*1000;
            $filter_to_jstime = strtotime($filter_to)*1000;

            $filter_from_obj = new \Zend_Date(strtotime($filter_from));
            $filter_to_obj = new \Zend_Date(strtotime($filter_to));

            $filter_from = $filter_from_obj->toString('MMMM dd, yyyy');
            $filter_to = $filter_to_obj->toString('MMMM dd, yyyy');
            $default_value = $filter_from." - ".$filter_to;
        }
        $current_year = date('Y');
        $next_year = (int)$current_year+1;   
        $fieldset->addType('date_range','Lof\AdvancedReports\Block\Adminhtml\System\Config\Form\Field\DateRange');
        $fieldset->addField('reportrange', 'date_range', [
            'label'         => __("Date Range"),
            'name'          => 'reportrange',
            'block_id'      => 'reportrange',
            'required'      => true,
            'default_value' => $default_value,
            'min_date'      => '01/01/1975',
            'max_date'      => '12/31/'.$next_year,
            'start_date'    => $filter_from_jstime,
            'end_date'      => $filter_to_jstime,
            'target_from'   => '#products_report_filter_from',
            'target_to'     => '#products_report_filter_to', 
            'field_style'   => 'background: #fff; cursor: pointer; padding: 10px 10px; border: 1px solid #ccc; width:300px ',
            'label_style'   =>  '',
        ]); 
  
        $fieldset->addField('show_order_statuses', 'select', [
            'name'      => 'show_order_statuses',
            'label'     => __('Order Status'),
            'options'   => array(
                    '0' => __('Any'),
                    '1' => __('Specified'),
                ),
            'note'      => __('Applies to Any of the Specified Order Statuses'),
        ], 'to');

        $fieldset->addField('order_statuses', 'multiselect',[
            'name'      => 'order_statuses',
            'values'    => $values,
            'label'     => __('Status'),
            'display'   => 'none'
        ], 'show_order_statuses');

        // define field dependencies
        if ($this->getFieldVisibility('show_order_statuses') && $this->getFieldVisibility('order_statuses')) { 
        $this->setChild('form_after', $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
                ->addFieldMap("{$htmlIdPrefix}show_order_statuses", 'show_order_statuses')
                ->addFieldMap("{$htmlIdPrefix}order_statuses", 'order_statuses')
                ->addFieldDependence('order_statuses', 'show_order_statuses', '1')

        );
        }

            if(!in_array($report_type, $notshow_actual)) {
            $fieldset->addField('show_actual_columns', 'select', array(
                'name'      => 'show_actual_columns',
                'options'   => array(
                    '1' => __('Yes'),
                    '0' => __('No')
                ),
                'label'     => __('Show Actual Values'),
                'title'     => __('Show Actual Values')
            ));
        }

 
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
