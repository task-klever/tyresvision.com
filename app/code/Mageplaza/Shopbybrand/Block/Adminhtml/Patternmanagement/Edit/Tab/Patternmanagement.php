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

namespace Mageplaza\Shopbybrand\Block\Adminhtml\Patternmanagement\Edit\Tab;

/**
 * Class Patternmanagement
 *
 * @package Mageplaza\Shopbybrand\Block\Adminhtml\Patternmanagement\Edit\Tab
 */
class Patternmanagement extends \Magento\Backend\Block\Widget\Form\Generic
    implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Config\Model\Config\Source\Enabledisable
     */
    protected $_booleanOptions;

    /**
     * @var \Mageplaza\Shopbybrand\Model\Config\Source\MetaRobots
     */
    public $metaRobotsOptions;
	
	/** @var \Magento\Cms\Model\Wysiwyg\Config  */
	protected $_wysiwygConfig;
	
	protected $eavConfig;

    /**
     * Patternmanagement constructor.
     *
     * @param \Mageplaza\Shopbybrand\Model\Config\Source\MetaRobots $metaRobotsOptions
     * @param \Magento\Config\Model\Config\Source\Enabledisable     $booleanOptions
     * @param \Magento\Backend\Block\Template\Context               $context
     * @param \Magento\Framework\Registry                           $registry
     * @param \Magento\Framework\Data\FormFactory                   $formFactory
     * @param \Magento\Store\Model\System\Store                     $systemStore
     * @param array                                                 $data
     */
    public function __construct(
        \Mageplaza\Shopbybrand\Model\Config\Source\MetaRobots $metaRobotsOptions,
        \Magento\Config\Model\Config\Source\Enabledisable $booleanOptions,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
		\Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
		\Magento\Eav\Model\Config $eavConfig,
		array $data = array()
    ) {
        $this->_booleanOptions = $booleanOptions;
        $this->_systemStore = $systemStore;
        $this->metaRobotsOptions = $metaRobotsOptions;
		$this->_wysiwygConfig = $wysiwygConfig;
		$this->_eavConfig = $eavConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /* @var $model \Mageplaza\Shopbybrand\Model\Patternmanagement */
        $model = $this->_coreRegistry->registry('current_brand_patternmanagement');
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('patternmanagement_');

        $fieldset = $form->addFieldset(
            'base_fieldset', array('legend' => __('Patternmanagement Information'))
        );

        if ($model->getPatternmanagementId()) {
            $fieldset->addField('patternmanagement_id', 'hidden', array('name' => 'patternmanagement_id'));
        }

        $fieldset->addField(
            'brand_id', 'select',
            ['name'     => 'brand_id', 'label' => __('Brand'), 'title' => __('Brand'),
             'required' => true,
			 'values' => $this->getAllBrandOptions()
			]
        );
		
		$fieldset->addField(
            'pattern_id', 'select',
            ['name'     => 'pattern_id', 'label' => __('Pattern'), 'title' => __('Pattern'),
             'required' => true,
			 'values' => $this->getAllPatternOptions()
			]
        );
		
		$fieldset->addField('image', 'image', [
				'name'  => 'image',
				'label' => __('Pattern Image'),
				'title' => __('Pattern Image'),
				'note'  => __('If empty, option visual image or default image from configuration will be used.')
			]
		);
		
		$fieldset->addField(
            'short_description', 'textarea',[
			'name'  => 'short_description',
			'label' => __('Short Description'),
             'title' => __('Short Description')
			]
        );
		
		$fieldset->addField(
            'description', 'textarea',
            [
			'name'  => 'description',
			'label' => __('Description'),
            'title' => __('Description')
			]
        );
		
		$fieldset->addField(
            'performance_description', 'editor',
            [
			'name'  => 'performance_description',
			'label' => __('Performance Description'),
            'title' => __('Performance Description'),
			'config' => $this->_wysiwygConfig->getConfig(['add_variables' => false, 'add_widgets' => false])
			]
        );
		
		$fieldset->addField(
            'dry', 'text',
            ['name'  => 'dry', 'label' => __('Dry'),
             'title' => __('Dry')]
        );
		
		$fieldset->addField(
            'wet', 'text',
            ['name'  => 'wet', 'label' => __('Wet'),
             'title' => __('Wet')]
        );
		
		$fieldset->addField(
            'sport', 'text',
            ['name'  => 'sport', 'label' => __('Sport'),
             'title' => __('Sport')]
        );
		
		$fieldset->addField(
            'comfort', 'text',
            ['name'  => 'comfort', 'label' => __('Comfort'),
             'title' => __('Comfort')]
        );
		
		$fieldset->addField(
            'mileage', 'text',
            ['name'  => 'mileage', 'label' => __('Mileage'),
             'title' => __('Mileage')]
        );
		
        $fieldset->addField(
            'url_key', 'text', ['name'  => 'url_key', 'label' => __('Url key'),
                                'title' => __('Url key')]
        );

        /* if (!$this->_storeManager->isSingleStoreMode()) {
            $fieldset->addField(
                'store_ids', 'multiselect',
                ['name'   => 'store_ids', 'label' => __('Stores view'),
                 'title'  => __('Stores view'),
                 'values' => $this->_systemStore->getStoreValuesForForm(
                     false, true
                 )]
            );
        } */

        $fieldset->addField(
            'status', 'select', ['name'   => 'status', 'label' => __('Status'),
                                 'title'  => __('Status'),
                                 'values' => $this->_booleanOptions->toOptionArray(
                                 )]
        );
        $fieldset->addField(
            'meta_title', 'text',
            ['name'  => 'meta_title', 'label' => __('Meta Title'),
             'title' => __('Meta Title')]
        );
        $fieldset->addField(
            'meta_keywords', 'text',
            ['name'  => 'meta_keywords', 'label' => __('Meta Keywords'),
             'title' => __('Meta Keywords')]
        );
        $fieldset->addField(
            'meta_description', 'textarea',
            ['name'  => 'meta_description', 'label' => __('Meta Description'),
             'title' => __('Meta Description')]
        );
        /* $fieldset->addField(
            'meta_robots', 'select',
            ['name'   => 'meta_robots', 'label' => __('Meta Robots'),
             'title'  => __('Meta Robots'),
             'values' => $this->metaRobotsOptions->toOptionArray(),]
        ); */
        if (!$model->getId()) {
            $model->addData(
                ['status' => 1, 'store_ids' => '0']
            );
        }

        $savedData = $model->getData();
        $form->setValues($savedData);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Patternmanagement');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Patternmanagement');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
	
	public function getAllBrandOptions()
	{
		$attributeCode = "brand";
		$attribute = $this->_eavConfig->getAttribute('catalog_product', $attributeCode);
		$options = $attribute->getSource()->getAllOptions();
		$arr = [];
		foreach ($options as $option) {
		    if ($option['value'] > 0) {
		        $arr[] = $option;
		    }
		}
		return $arr;
	}
	
	public function getAllPatternOptions()
	{
		$attributeCode = "pattern";
		$attribute = $this->_eavConfig->getAttribute('catalog_product', $attributeCode);
		$options = $attribute->getSource()->getAllOptions();
		$arr = [];
		foreach ($options as $option) {
		    if ($option['value'] > 0) {
		        $arr[] = $option;
		    }
		}
		return $arr;
	}
}
