<?php
namespace Bitbull\Tooso\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Indexer\StateInterface;
use Magento\Indexer\Model\Indexer\StateFactory;
use Bitbull\Tooso\Model\Indexer\Catalog;
use Bitbull\Tooso\Model\Indexer\Stock;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var StateFactory
     */
    private $stateFactory;

    /**
     * InstallSchema constructor.
     * @param StateFactory $stateFactory
     */
    public function __construct(StateFactory $stateFactory)
    {
        $this->stateFactory = $stateFactory;
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @throws \Exception
     */
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $setup->startSetup();

        /**
         * Init catalog index status
         */

        $state = $this->stateFactory->create();
        $state->loadByIndexer(Catalog::INDEX_NAME);
        $state->setHashConfig('');
        $state->setStatus(StateInterface::STATUS_INVALID);
        $state->save(); //TODO: fix deprecation

        /**
         * Init stock index status
         */

        $state = $this->stateFactory->create();
        $state->loadByIndexer(Stock::INDEX_NAME);
        $state->setHashConfig('');
        $state->setStatus(StateInterface::STATUS_INVALID);
        $state->save(); //TODO: fix deprecation

        $setup->endSetup();
    }
}

