<?php
namespace Bitbull\Tooso\Setup;

use Bitbull\Tooso\Model\Service\Indexer\Db\AttributesValuesIndexFlat;
use Bitbull\Tooso\Model\Service\Indexer\Db\CatalogIndexFlat;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.1.0', '<=')) {
            $this->upgrade_1_1_0($setup);
        }

        if (version_compare($context->getVersion(), '1.2.0', '<=')) {
            $this->upgrade_1_2_0($setup);
        }

        $setup->endSetup();
    }

    /**
     * Upgrade to 1.1.0
     *
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    protected function upgrade_1_1_0(SchemaSetupInterface $setup)
    {

        /**
         * Create catalog index flat table
         */

        $table = $setup->getConnection()->newTable(
            $setup->getTable(CatalogIndexFlat::TABLE_NAME)
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [
                'nullable' => false,
                'primary'   => true,
            ],
            'Store ID'
        )->addColumn(
            'sku',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [
                'nullable' => false,
                'primary'   => true,
            ],
            'Product SKU'
        )->addColumn(
            'data',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [
            ],
            'Data'
        )->addColumn(
            'update_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [
            ],
            'Modification Time'
        );

        $setup->getConnection()->createTable($table);
    }

    /**
     * Upgrade to 1.2.0
     *
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     */
    protected function upgrade_1_2_0(SchemaSetupInterface $setup)
    {

        /**
         * Create attributes index flat table
         */

        $table = $setup->getConnection()->newTable(
            $setup->getTable(AttributesValuesIndexFlat::TABLE_NAME)
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [
                'nullable' => false,
                'primary'   => true,
            ],
            'Store ID'
        )->addColumn(
            'attribute_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [
                'nullable' => false,
                'primary'   => true,
            ],
            'Attribute value ID'
        )->addColumn(
            'value',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            [
            ],
            'Attribute value'
        )->addColumn(
            'update_time',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            [
            ],
            'Modification Time'
        );

        $setup->getConnection()->createTable($table);
    }
}
