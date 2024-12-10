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

namespace Mageplaza\Shopbybrand\Block\Adminhtml\Patternmanagement;

/**
 * CMS block edit form container
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
	/**
	 * Core registry
	 *
	 * @var \Magento\Framework\Registry
	 */
	public $_coreRegistry;

	/**
	 * constructor
	 *
	 * @param \Magento\Framework\Registry $coreRegistry
	 * @param \Magento\Backend\Block\Widget\Context $context
	 * @param array $data
	 */
	public function __construct(
		\Magento\Framework\Registry $coreRegistry,
		\Magento\Backend\Block\Widget\Context $context,
		array $data = []
	)
	{
		$this->_coreRegistry = $coreRegistry;
		parent::__construct($context, $data);
	}

	protected function _construct()
	{
		$this->_objectId   = 'patternmanagement_id';
		$this->_blockGroup = 'Mageplaza_Shopbybrand';
		$this->_controller = 'adminhtml_patternmanagement';

		parent::_construct();

		$this->buttonList->update('save', 'label', __('Save Pattern'));
		$this->buttonList->update('delete', 'label', __('Delete Pattern'));

		$this->buttonList->add(
			'saveandcontinue',
			array(
				'label'          => __('Save and Continue Edit'),
				'class'          => 'save',
				'data_attribute' => array(
					'mage-init' => array('button' => array('event' => 'saveAndContinueEdit', 'target' => '#edit_form'))
				)
			),
			-100
		);
	}

	/**
	 * Get edit form container header text
	 *
	 * @return string
	 */
	public function getHeaderText()
	{
		if ($this->_coreRegistry->registry('current_brand_patternmanagement')->getId()) {
			return __("Edit Pattern '%1'", $this->escapeHtml($this->_coreRegistry->registry('current_brand_patternmanagement')->getName()));
		} else {
			return __('New Pattern');
		}
	}
}
