<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Daan Rijpkema <d.rijpkema@bluem.nl>
 */

namespace Bluem\Integration\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Catalog\Model\Product\Attribute\Backend\Media\ImageEntryConverter;
use \Magento\Framework\DB\Ddl\Table;
use \Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (!$setup->tableExists('bluem_integration_request')) {
            $table = $setup->getConnection()->newTable(
                $setup->getTable('bluem_integration_request')
            )
                ->addColumn(
                    'request_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                    ],
                    'Identity ID'
                )
                ->addColumn(
                    'user_id',
                    Table::TYPE_INTEGER,
                    null,
                    [],
                    'Post Status'
                )
                ->addColumn(
                    'transaction_id',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable => false'],
                    'Transaction ID'
                )
                ->addColumn(
                    'entrance_code',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable => false'],
                    'Entrance Code'
                )
                ->addColumn(
                    'transaction_url',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable => false'],
                    'Transaction URL'
                )
                ->addColumn(
                    'description',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => false,
                        'default' => null
                    ],
                    'Transaction URL'
                )
                ->addColumn(
                    'debtor_reference',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => false,
                        'default' => null
                    ],
                    'Transaction URL'
                )
                ->addColumn(
                    'type',
                    Table::TYPE_TEXT,
                    32,
                    ['default' => 'identity'],
                    'Request Status'
                )
                ->addColumn(
                    'environment',
                    Table::TYPE_TEXT,
                    4,
                    ['default' => 'test'],
                    'Request environment (test or prod)'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    32,
                    ['default' => 'open'],
                    'Request Status'
                )
                ->addColumn(
                    'payload',
                    Table::TYPE_TEXT,
                    5120,
                    ['default' => ''],
                    'Request payload'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                    'Updated At'
                )
                ->setComment('Post Table');
            $setup->getConnection()->createTable($table);

            // $setup->getConnection()->addIndex(
            // 	$setup->getTable('bluem_integration_request'),
            // 	$setup->getIdxName(
            // 		$setup->getTable('bluem_integration_request'),
            // 		['user_id','transaction_id','entrance_code','transaction_url','status' ],
            // 		AdapterInterface::INDEX_TYPE_FULLTEXT
            // 	),
            // 	['user_id','transaction_id','entrance_code','transaction_url','status' ],
            // 	AdapterInterface::INDEX_TYPE_FULLTEXT
            // );
        }



        if ($setup->tableExists('bluem_integration_request')) {
            $tableName = $setup->getTable('bluem_integration_request');
            $connection = $setup->getConnection();
            if (!$connection->tableColumnExists($tableName, 'order_id')) {
                $connection->addColumn(
                    $tableName,
                    'order_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => true,
                        'default' => null,
                        'comment' => 'Added by Bluem for the ePayment method',
                        'after' => 'entrance_code',
                        'length' => 11
                    ]
                );
            }
        }


        $setup->endSetup();
    }
}
