<?php
namespace MageSuite\BackInStock\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();


        $table = $installer->getConnection()->newTable(
            $installer->getTable('back_in_stock_subscription_entity')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Back in stock id'
        )->addColumn(
            'customer_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Customer id'
        )->addColumn(     'customer_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => ''],
            'Customer email'
        )->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Product id'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Store Id'
        )->addColumn(
            'add_date',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
            'Product alert add date'
        )->addColumn(
            'send_date',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [],
            'Product alert send date'
        )->addColumn(
            'send_count',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Send Count'
        )->addColumn(
            'was_notification_sent',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Product alert status'
        )->addColumn(
            'customer_confirmed',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Is Customer Confirmed'
        )->addColumn(
            'token',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => ''],
            'Token'
        )->addIndex(
            $installer->getIdxName('back_in_stock_subscription_entity', ['product_id']),
            ['product_id']
        )->addIndex(
            $installer->getIdxName('back_in_stock_subscription_entity', ['store_id']),
            ['store_id']
        )->addForeignKey(
            $installer->getFkName('back_in_stock_subscription_entity', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('back_in_stock_subscription_entity', 'product_id', 'catalog_product_entity', 'entity_id'),
            'product_id',
            $installer->getTable('catalog_product_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Back in stock subscriptions'
        );
        $installer->getConnection()->createTable($table);


        $table = $installer->getConnection()->newTable(
            $installer->getTable('back_in_stock_notification_queue')
        )->addColumn(
            'id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Back in stock id'
        )->addColumn(     'subscription_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Back in stock subscription id'
        )->addColumn(     'customer_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => ''],
            'Customer email'
        )->addColumn(     'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Store Id'
        )->addColumn(     'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Product Id'
        )->addColumn(     'notification_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => ''],
            'Notification type'
        )->addColumn(     'message',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => ''],
            'Message'
        )->addForeignKey(
            $installer->getFkName('back_in_stock_notification_queue', 'product_id', 'catalog_product_entity', 'entity_id'),
            'product_id',
            $installer->getTable('catalog_product_entity'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('back_in_stock_notification_queue', 'store_id', 'store', 'store_id'),
            'store_id',
            $installer->getTable('store'),
            'store_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('back_in_stock_notification_queue', 'subscription_id', 'back_in_stock_subscription_entity', 'id'),
            'subscription_id',
            $installer->getTable('back_in_stock_subscription_entity'),
            'id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        );

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
