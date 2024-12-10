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

namespace Mageplaza\Shopbybrand\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 * @package Mageplaza\Shopbybrand\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
	/**
	 * {@inheritdoc}
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
	{
		$installer = $setup;
		$installer->startSetup();

		if (version_compare($context->getVersion(), '2.2.0', '<')) {
			if (!$installer->tableExists('mageplaza_shopbybrand_category')) {
				$table = $installer->getConnection()->newTable($installer->getTable('mageplaza_shopbybrand_category'))
					->addColumn('cat_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true])
					->addColumn('name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '256', [])
					->addColumn('status', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, ['nullable' => false, 'default' => 1])
					->addColumn('url_key', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '255', [])
					->addColumn('store_ids', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '255', ['nullable' => false, 'default' => '0'])
					->addColumn('meta_title', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '256', [])
					->addColumn('meta_keywords', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', [])
					->addColumn('meta_description', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '2M', [])
                    ->addColumn(
                        'meta_robots',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        null,
                        [],
                        'Category Meta Robots'
                    )
					->addColumn('created_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Category Created At')
					->addColumn('updated_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Tag Updated At')
					->addIndex($installer->getIdxName('mageplaza_shopbybrand_category', 'url_key'), 'url_key', ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE])
					->setComment('Mageplaza Shopbybrand category table');

				$installer->getConnection()->createTable($table);
			}

			if (!$installer->tableExists('mageplaza_shopbybrand_brand_category')) {
				$table = $installer->getConnection()->newTable($installer->getTable('mageplaza_shopbybrand_brand_category'))
					->addColumn('cat_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false, 'primary' => true,])
					->addColumn('option_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['nullable' => false, 'unsigned' => true, 'primary'  => true,])
					->addColumn('position', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['nullable' => false, 'default' => 0])
					->addIndex(
						$installer->getIdxName('mageplaza_shopbybrand_brand_category', ['option_id']),
						['option_id']
					)
					->addIndex(
						$installer->getIdxName('mageplaza_shopbybrand_brand_category', ['cat_id']),
						['cat_id']
					)
					->addForeignKey(
						$installer->getFkName(
							'mageplaza_shopbybrand_brand_category',
							'option_id',
							'eav_attribute_option',
							'option_id'
						),
						'option_id',
						$installer->getTable('eav_attribute_option'),
						'option_id',
						\Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
						\Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
					)
					->addForeignKey(
						$installer->getFkName(
							'mageplaza_shopbybrand_brand_category',
							'cat_id',
							'mageplaza_shopbybrand_category',
							'cat_id'
						),
						'cat_id',
						$installer->getTable('mageplaza_shopbybrand_category'),
						'cat_id',
						\Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
						\Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
					)
					->addIndex(
						$installer->getIdxName(
							'mageplaza_shopbybrand_brand_category',
							[
								'option_id',
								'cat_id'
							],
							\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
						),
						[
							'option_id',
							'cat_id'
						],
						[
							'type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
						]
					)
					->setComment('Mageplaza Shopbybrand brand category table');

				$installer->getConnection()->createTable($table);
			}
		}
		if (version_compare($context->getVersion(), '2.2.1') < 0) {
            $tableName = $setup->getTable('mageplaza_brand');

            if ($setup->getConnection()->isTableExists($tableName) == true) {
                // Declare data
                $columns = [
                    'status' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 1,
                        'nullable' => false,
                        'default' => 1,
                        'nullable' => false,
                        'comment' => 'status',
                    ],
                ];

                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableName, $name, $definition);
                }

            }
        }
		if (version_compare($context->getVersion(), '2.2.2') < 0) {
            $table = $installer->getConnection()->newTable($installer->getTable('mageplaza_shopbybrand_patternmanagement'))
					->addColumn('patternmanagement_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true])
					->addColumn('brand_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => false])
					->addColumn('brand', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '256', [])
					->addColumn('pattern_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => false])
					->addColumn('pattern', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '256', [])
					->addColumn('image', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '256', [])
					->addColumn('short_description', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '2M', [])
					->addColumn('description', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '2M', [])
					->addColumn('performance_description', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '2M', [])
					->addColumn('dry', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => false])
					->addColumn('wet', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => false])
					->addColumn('sport', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => false])
					->addColumn('comfort', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => false])
					->addColumn('mileage', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['unsigned' => true, 'nullable' => false])
					->addColumn('url_key', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '255', [])
					->addColumn('meta_title', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '256', [])
					->addColumn('meta_keywords', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '64k', [])
					->addColumn('meta_description', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '2M', [])
					->addColumn('status', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, ['nullable' => false, 'default' => 1])
					->addColumn('created_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Created At')
					->addColumn('updated_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Updated At')
					->setComment('Mageplaza Shopbybrand Pattern table');

				$installer->getConnection()->createTable($table);
        }

		$installer->endSetup();
	}
}
