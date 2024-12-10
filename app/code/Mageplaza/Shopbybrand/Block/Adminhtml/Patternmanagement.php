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
namespace Mageplaza\Shopbybrand\Block\Adminhtml;

/**
 * Class Patternmanagement
 * @package Mageplaza\Shopbybrand\Block\Adminhtml
 */
class Patternmanagement extends \Magento\Backend\Block\Widget\Grid\Container
{
	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_controller     = 'adminhtml_patternmanagement';/*block grid.php directory*/
		$this->_blockGroup     = 'Mageplaza_Shopbybrand';
		$this->_headerText     = __('Brand Patternmanagement');
		$this->_addButtonLabel = __('Add New Pattern');

		parent::_construct();
	}
}
