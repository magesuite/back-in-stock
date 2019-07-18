<?php
namespace MageSuite\BackInStock\Setup;

class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{
    public function upgrade(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $this->addCustomerIdToNotificationQueue($setup);
        }

        $setup->endSetup();
    }

    protected function addCustomerIdToNotificationQueue($setup)
    {
        if ($setup->getConnection()->tableColumnExists($setup->getTable('back_in_stock_notification_queue'), 'customer_id') === false) {
            $setup->getConnection()->addColumn(
                $setup->getTable('back_in_stock_notification_queue'),
                'customer_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 10,
                    'unsigned' => true,
                    'nullable' => false,
                    'default'  => '0',
                    'comment' => 'Customer id'
                ]
            );
        }
    }
}
