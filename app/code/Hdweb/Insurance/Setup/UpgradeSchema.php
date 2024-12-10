<?php

namespace Hdweb\Insurance\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.0.2') < 0) {

            $installer->getConnection()->addColumn(
                $installer->getTable('hdweb_insurance'),
                'request_parameter_application',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'LENGTH' =>'2M',
                    'default'  => null,
                    'comment'  => 'Request Parameter Application',
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable('hdweb_insurance'),
                'response_parameter_application',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'LENGTH' =>'2M',
                    'default'  => null,
                    'comment'  => 'Response Parameter Application',
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.3') < 0) {

            $installer->getConnection()->addColumn(
                $installer->getTable('hdweb_insurance'),
                'request_parameter_policy',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'LENGTH' =>'2M',
                    'default'  => null,
                    'comment'  => 'Request Parameter Policy',
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable('hdweb_insurance'),
                'response_parameter_policy',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'LENGTH' =>'2M',
                    'default'  => null,
                    'comment'  => 'Response Parameter Policy',
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.4') < 0) {

            $installer->getConnection()->addColumn(
                $installer->getTable('hdweb_insurance'),
                'year',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Year',
                ]
            );

            $installer->getConnection()->addColumn(
                $installer->getTable('hdweb_insurance'),
                'vehicle_plate_no',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Vehicle Plate No',
                ]
            );
        }

        $installer->endSetup();
    }

}
