<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) 2017 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Block\Adminhtml\Patternmanagement\Edit;

/**
 * Class Tabs
 * @package Mageplaza\Shopbybrand\Block\Adminhtml\ProductsPage\Edit
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
	protected function _construct()
	{
		parent::_construct();
		$this->setId('brand_patternmanagement_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(__('Patternmanagement'));
	}
}