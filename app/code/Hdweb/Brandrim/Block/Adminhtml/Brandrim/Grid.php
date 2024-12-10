<?php
namespace Hdweb\Brandrim\Block\Adminhtml\Brandrim;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Hdweb\Brandrim\Model\brandrimFactory
     */
    protected $_brandrimFactory;

    /**
     * @var \Hdweb\Brandrim\Model\Status
     */
    protected $_status;

    protected $storelocator;

    protected $_eavConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Hdweb\Brandrim\Model\brandrimFactory $brandrimFactory
     * @param \Hdweb\Brandrim\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Hdweb\Brandrim\Model\BrandrimFactory $BrandrimFactory,
        \Hdweb\Brandrim\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        \Ecomteck\StoreLocator\Model\StoresFactory $storelocator,
        \Magento\Eav\Model\Config $eavConfig,
        array $data = []
    ) {
        $this->_brandrimFactory = $BrandrimFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        $this->storelocator = $storelocator;
        $this->_eavConfig = $eavConfig;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_brandrimFactory->create()->getCollection();
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );


		

						$this->addColumn(
							'installerid',
							[
								'header' => __('Installerid'),
								'index' => 'installerid',
								'type' => 'options',
								'options' => \Hdweb\Brandrim\Block\Adminhtml\Brandrim\Grid::getOptionArray0()
							]
						);

						

						$this->addColumn(
							'brand',
							[
								'header' => __('Brand'),
								'index' => 'brand',
								'type' => 'options',
								'options' => \Hdweb\Brandrim\Block\Adminhtml\Brandrim\Grid::getOptionArray1()
							]
						);

						

						$this->addColumn(
							'rim',
							[
								'header' => __('Rim'),
								'index' => 'rim',
								'type' => 'options',
								'options' => \Hdweb\Brandrim\Block\Adminhtml\Brandrim\Grid::getOptionArray2()
							]
						);

						
				$this->addColumn(
					'qty',
					[
						'header' => __('Qty'),
						'index' => 'qty',
					]
				);
				
				$this->addColumn(
					'shipping_amount',
					[
						'header' => __('Shipping Amount'),
						'index' => 'shipping_amount',
					]
				);
				

						$this->addColumn(
							'status',
							[
								'header' => __('Status'),
								'index' => 'status',
								'type' => 'options',
								'options' => \Hdweb\Brandrim\Block\Adminhtml\Brandrim\Grid::getOptionArray5()
							]
						);

						
				$this->addColumn(
					'startdate',
					[
						'header' => __('Startdate'),
						'index' => 'startdate',
					]
				);
				
				$this->addColumn(
					'enddate',
					[
						'header' => __('Eddate'),
						'index' => 'enddate',
					]
				);
				


		
        //$this->addColumn(
            //'edit',
            //[
                //'header' => __('Edit'),
                //'type' => 'action',
                //'getter' => 'getId',
                //'actions' => [
                    //[
                        //'caption' => __('Edit'),
                        //'url' => [
                            //'base' => '*/*/edit'
                        //],
                        //'field' => 'id'
                    //]
                //],
                //'filter' => false,
                //'sortable' => false,
                //'index' => 'stores',
                //'header_css_class' => 'col-action',
                //'column_css_class' => 'col-action'
            //]
        //);
		

		
		   $this->addExportType($this->getUrl('brandrim/*/exportCsv', ['_current' => true]),__('CSV'));

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

	
    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {

        $this->setMassactionIdField('id');
        //$this->getMassactionBlock()->setTemplate('Hdweb_Brandrim::brandrim/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('brandrim');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('brandrim/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->getOptionArray();

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('brandrim/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $statuses
                    ]
                ]
            ]
        );


        return $this;
    }
		

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('brandrim/*/index', ['_current' => true]);
    }

    /**
     * @param \Hdweb\Brandrim\Model\brandrim|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
		
        return $this->getUrl(
            'brandrim/*/edit',
            ['id' => $row->getId()]
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
		 public function getValueArray0()
		{ 
            $data_array=array();
			foreach(\Hdweb\Brandrim\Block\Adminhtml\Brandrim\Grid::getOptionArray0() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);
			}
            return($data_array);

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
		 public function getValueArray1()
		{

            $attributeCode = "mgs_brand";
        $attribute = $this->_eavConfig->getAttribute('catalog_product', $attributeCode);
        $options = $attribute->getSource()->getAllOptions();
        $arr = [];
                foreach ($options as $option) {
            if ($option['value'] > 0) {
                $arr[] =  ['value' => $option['value'], 'label' => $option['label']];
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
		static public function getValueArray2()
		{
            $data_array=array();
			foreach(\Hdweb\Brandrim\Block\Adminhtml\Brandrim\Grid::getOptionArray2() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);
			}
            return($data_array);

		}
		
		 public function getOptionArray5()
		{
            $data_array=array(); 
			$data_array[1]='Yes';
                  $data_array[2]='No';

            return($data_array);
		}
		static public function getValueArray5()
		{
            $data_array=array();
			foreach(\Hdweb\Brandrim\Block\Adminhtml\Brandrim\Grid::getOptionArray5() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);
			}
            return($data_array);

		}
		

}