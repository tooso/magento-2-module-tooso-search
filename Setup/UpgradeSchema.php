<?php
namespace Bitbull\Tooso\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Bitbull\Tooso\Model\Service\Indexer\Db\CatalogIndexFlat;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context ) {

        if(version_compare($context->getVersion(), '1.1.0', '<=')) {

            /**
             * Create catalog index flat table
             */

            $table = $setup->getConnection()->newTable(
                $setup->getTable(CatalogIndexFlat::TABLE_NAME)
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                255,
                array (
                    'nullable' => false,
                    'primary'   => true,
                ),
                'Store ID'
            )->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                255,
                array (
                    'nullable' => false,
                    'primary'   => true,
                ),
                'Product entity ID'
            )->addColumn(
                'data',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                array (
                ),
                'Data'
            )->addColumn(
                'update_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                array (
                ),
                'Modification Time'
            );

            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
