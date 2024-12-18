<?php
/**
 * @category   Hdweb
 * @package    Hdweb_Insurance
 * @author     vicky.hdit@gmail.com
 * @copyright  This file was generated by using Module Creator(http://code.vky.co.in/magento-2-module-creator/) provided by VKY <viky.031290@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Hdweb\Insurance\Block;

use Magento\Framework\View\Element\Template\Context;
use Hdweb\Insurance\Model\InsuranceFactory;
/**
 * Insurance List block
 */
class InsuranceListData extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Insurance
     */
    protected $_insurance;
    public function __construct(
        Context $context,
        InsuranceFactory $insurance
    ) {
        $this->_insurance = $insurance;
        parent::__construct($context);
    }

    public function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Hdweb Insurance Module List Page'));
        
        if ($this->getInsuranceCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'hdweb.insurance.pager'
            )->setAvailableLimit(array(5=>5,10=>10,15=>15))->setShowPerPage(true)->setCollection(
                $this->getInsuranceCollection()
            );
            $this->setChild('pager', $pager);
            $this->getInsuranceCollection()->load();
        }
        return parent::_prepareLayout();
    }

    public function getInsuranceCollection()
    {
        $page = ($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit'))? $this->getRequest()->getParam('limit') : 5;

        $insurance = $this->_insurance->create();
        $collection = $insurance->getCollection();
        $collection->addFieldToFilter('status','1');
        //$insurance->setOrder('insurance_id','ASC');
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);

        return $collection;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}