<?php
/**
 * Bluem Integration - Magento2 Module
 * (C) Bluem 2021
 *
 * @category Module
 * @author   Daan Rijpkema <d.rijpkema@bluem.nl>
 */

namespace Bluem\Integration\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
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
                    'order_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => true,
                        'default' => null,
                        'comment' => 'Added by Bluem for the ePayment method',
                    ],
                    'Order ID'
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
                ->setComment('Requests Table for Bluem');
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




        if ($setup->tableExists('quote_payment')) {
            $tableName = $setup->getTable('quote_payment');
            $connection = $setup->getConnection();
            if (!$connection->tableColumnExists($tableName, 'assistant_id')) {
                $connection->addColumn(
                    $tableName,
                    'assistant_id',
                    [
                        'type' => Table::TYPE_TEXT,
                        'nullable' => true,
                        'default' => null,
                        'comment' => 'Added by Bluem for the ePayment method',
                        'after' => 'po_number',
                        'length' => 255
                    ]
                );
            }
        }

        if ($setup->tableExists('sales_order_payment')) {
            $tableName = $setup->getTable('sales_order_payment');
            $connection = $setup->getConnection();
            if (!$connection->tableColumnExists($tableName, 'assistant_id')) {
                $connection->addColumn(
                    $tableName,
                    'assistant_id',
                    [
                        'type' => Table::TYPE_TEXT,
                        'nullable' => true,
                        'default' => null,
                        'comment' => 'Added by Bluem for the ePayment method',
                        'after' => 'po_number',
                        'length' => 255
                    ]
                );
            }
        }
        $setup->endSetup();
    }
}
