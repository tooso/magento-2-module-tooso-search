<?php
namespace Bitbull\Tooso\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Bitbull\Tooso\Model\Service\Indexer\Db\CatalogIndexFlat;
use Bitbull\Tooso\Model\Service\Indexer\Db\AttributesValuesIndexFlat;

class Uninstall implements UninstallInterface
{
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /**
         * Remove catalog index flat table
         */
        $setup->getConnection()->dropTable(CatalogIndexFlat::TABLE_NAME);
        /**
         * Remove attributes index flat table
         */
        $setup->getConnection()->dropTable(AttributesValuesIndexFlat::TABLE_NAME);

        $setup->endSetup();
    }
}
