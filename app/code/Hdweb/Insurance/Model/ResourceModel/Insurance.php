<?php
/**
 * @category   Hdweb
 * @package    Hdweb_Insurance
 * @author     vicky.hdit@gmail.com
 * @copyright  This file was generated by using Module Creator(http://code.vky.co.in/magento-2-module-creator/) provided by VKY <viky.031290@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Hdweb\Insurance\Model\ResourceModel;

class Insurance extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('hdweb_insurance', 'insurance_id');   //here "hdweb_insurance" is table name and "insurance_id" is the primary key of custom table
    }
}