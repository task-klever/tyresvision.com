<?php
/**
 * Ecomteck_StoreLocator extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category  Ecomteck
 * @package   Ecomteck_StoreLocator
 * @copyright 2016 Ecomteck
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 * @author    Ecomteck
 */
 
namespace Ecomteck\StoreLocator\Setup;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Config\Model\ResourceModel\Config\Data;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;

/**
 * @codeCoverageIgnore
 */
 
class Uninstall implements UninstallInterface
{
    /**
     * @var CollectionFactory
     */
    public $collectionFactory;
    /**
     * @var Data
     */
    public $configResource;
    /**
     * @param CollectionFactory $collectionFactory
     * @param Data $configResource
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Data $configResource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->configResource    = $configResource;
    }
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.Generic.CodeAnalysis.UnusedFunctionParameter)
     */
    // @codingStandardsIgnoreStart
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    // @codingStandardsIgnoreEnd
    {
        //remove tables
        if ($setup->tableExists('ecomteck_storelocator_stores')) {
            $setup->getConnection()->dropTable('ecomteck_storelocator_stores');
        }
        //remove config settings if any
        $collection = $this->collectionFactory->create()
            ->addPathFilter('ecomteck_storelocator_stores');
        foreach ($collection as $config) {
            $this->deleteConfig($config);
        }
    }
    /**
     * @param AbstractModel $config
     * @throws \Exception
     */
    public function deleteConfig(AbstractModel $config)
    {
        $this->configResource->delete($config);
    }
}
