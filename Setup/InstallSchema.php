<?php
namespace Bitbull\Tooso\Setup;

use Magento\Framework\Indexer\StateInterface;
use Magento\Indexer\Model\Indexer\StateFactory;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     * @var StateFactory
     */
    private $stateFactory;

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
        $installer = $setup;
        $installer->startSetup();

        $state = $this->stateFactory->create();
        $state->loadByIndexer('tooso_catalog');
        $state->setHashConfig('');
        $state->setStatus(StateInterface::STATUS_INVALID);
        $state->save();

        $installer->endSetup();
    }
}

