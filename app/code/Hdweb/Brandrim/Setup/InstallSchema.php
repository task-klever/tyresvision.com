<?php

namespace Hdweb\Brandrim\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.0') < 0){

		$installer->run('CREATE TABLE `installer_brand_rim` (
  `id` int(10) UNSIGNED NOT NULL COMMENT \'Id\',
  `installerid` varchar(255) DEFAULT \'\' COMMENT \'Installerid\',
  `brand` varchar(255) DEFAULT \'\' COMMENT \'Brand\',
  `rim` varchar(255) DEFAULT \'\' COMMENT \'Rim\',
  `qty` varchar(255) DEFAULT \'\' COMMENT \'Qty\',
  `shipping_amount` varchar(255) DEFAULT \'\' COMMENT \'Shipping_amount\',
  `status` varchar(255) DEFAULT \'\' COMMENT \'Status\',
  `startdate` varchar(255) DEFAULT \'\' COMMENT \'Startdate\',
  `enddate` varchar(255) DEFAULT \'\' COMMENT \'Enddate\'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT=\'installer_brand_rim\'');


		

		}

        $installer->endSetup();

    }
}