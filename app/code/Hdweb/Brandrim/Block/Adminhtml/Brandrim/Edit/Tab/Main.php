<?php

namespace Hdweb\Brandrim\Block\Adminhtml\Brandrim\Edit\Tab;

/**
 * Brandrim edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Hdweb\Brandrim\Model\Status
     */
    protected $_status;

    protected $storelocator;

    protected $_eavConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Hdweb\Brandrim\Model\Status $status,
        \Ecomteck\StoreLocator\Model\StoresFactory $storelocator,
        \Magento\Eav\Model\Config $eavConfig,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
        $this->storelocator = $storelocator;
        $this->_eavConfig = $eavConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Hdweb\Brandrim\Model\BlogPosts */
        $model = $this->_coreRegistry->registry('brandrim');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

		

        $fieldset->addField(
            'installerid',
            'select',
            [
                'label' => __('Installerid'),
                'title' => __('Installerid'),
                'name' => 'installerid',
				'required' => true,
                'options' => $this->getOptionArray0(),
                'disabled' => $isElementDisabled
            ]
        );

						

        $fieldset->addField(
            'brand',
            'select',
            [
                'label' => __('Brand'),
                'title' => __('Brand'),
                'name' => 'brand',
				'required' => true,
                'options' => $this->getOptionArray1(),
                'disabled' => $isElementDisabled
            ]
        );

						

        $fieldset->addField(
            'rim',
            'select',
            [
                'label' => __('Rim'),
                'title' => __('Rim'),
                'name' => 'rim',
				'required' => true,
                'options' => $this->getOptionArray2(),
                'disabled' => $isElementDisabled
            ]
        );

						
        $fieldset->addField(
            'qty',
            'text',
            [
                'name' => 'qty',
                'label' => __('Qty'),
                'title' => __('Qty'),
				'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
					
        $fieldset->addField(
            'shipping_amount',
            'text',
            [
                'name' => 'shipping_amount',
                'label' => __('Shipping Amount'),
                'title' => __('Shipping Amount'),
				'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
					

        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
				'required' => true,
                'options' => $this->getOptionArray5(),
                'disabled' => $isElementDisabled
            ]
        );

        $dateFormat = 'y-M-d';


        $timeFormat ='H:i:s';
						
        $fieldset->addField(
            'startdate',
            'date',
            [
                'name' => 'startdate',
                'label' => __('Startdate'),
                'title' => __('Startdate'),
				 'date_format' => $dateFormat,
              //   'time_format' => $timeFormat,
                'disabled' => $isElementDisabled
            ]
        );
					
          
                 
        $fieldset->addField(
            'enddate',
            'date',
            [
                'name' => 'enddate',
                'label' => __('Eddate'),
                'title' => __('Eddate'),
                'date_format' => $dateFormat,
                //'time_format' => $timeFormat,
                'disabled' => $isElementDisabled
            ]
        );
					

        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Item Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Item Information');
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

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    public function getTargetOptionArray(){
    	return array(
    				'_self' => "Self",
					'_blank' => "New Page",
    				);
    }

    public function getOptionArray0()
     {
            $allstores = $this->storelocator->create()->getCollection();
            $arr =array();
            foreach ($allstores as $option) {
            
                    $arr[$option->getId()] = $option->getName(); //['value' => $option->getId(), 'label' => $option->getName()];
            
            }

        return $arr;

     }

      public function getOptionArray1()
        {
  
            $attributeCode = "mgs_brand";
            $attribute = $this->_eavConfig->getAttribute('catalog_product', $attributeCode);
            $options = $attribute->getSource()->getAllOptions();
            $arr = [];
                    foreach ($options as $option) {
                if ($option['value'] > 0) {
                    $arr[$option['value']] =  $option['label'];//['value' => $option['value'], 'label' => $option['label']];
                }
            }

            return $arr;

     }

      public function getOptionArray2()
      {
		$attributeCode= "rim";
		$attribute = $this->_eavConfig->getAttribute('catalog_product', $attributeCode);
		$options = $attribute->getSource()->getAllOptions();
		$arr = [];
		foreach ($options as $option) {
			if ($option['value'] > 0) {
				$arr[ $option['value']] = $option['label'];
			}
		}

			return $arr;
        }
		
		public function getOptionArray5()
		{
            $data_array = array(); 
			$data_array[1]='Yes';
            $data_array[2]='No';
            return $data_array;
		}
}
