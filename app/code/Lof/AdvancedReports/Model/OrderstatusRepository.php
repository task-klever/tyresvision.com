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
use Lof\AdvancedReports\Api\OrderstatusInterface;
use Magento\Framework\Api\SortOrder;
 
class OrderstatusRepository implements OrderstatusInterface
{
    protected $_objectManager;
     /**
     * Class constructor
     *
     */ 
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
        ) {
        $this->_objectManager = $objectManager;
    }
    /**
     * @return array
     */
    public function getStatus() {
        $statuses = $this->_objectManager->create('Magento\Sales\Model\Order\Config')->getStatuses(); 

        $searchResult = array();
        foreach ($statuses as $code => $label) {
            $searchResult[] = array(
                    'label' => __($label),
                    'value' => $code
                );
        }
        return $searchResult;
    }

}