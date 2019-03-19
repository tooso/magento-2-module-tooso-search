<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Indexer;

use Bitbull\Tooso\Api\Service\Indexer\CatalogInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;

class Catalog implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    const INDEX_NAME = 'tooso_catalog';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CatalogInterface
     */
    protected $catalog;

    /**
     * @param LoggerInterface $logger
     * @param CatalogInterface $catalog
     */
    public function __construct(
        LoggerInterface $logger,
        CatalogInterface $catalog
    )
    {
        $this->logger = $logger;
        $this->catalog = $catalog;
    }

    /*
     * Used by mview, allows process indexer in the "Update on schedule" mode
     */
    public function execute($ids){
        //Used by mview, allows you to process multiple placed orders in the "Update on schedule" mode
        $this->logger->debug('[indexer catalog] asked to execute reindex for: '.implode(',', $ids));
        $this->catalog->execute($ids);
        $this->logger->debug('[indexer catalog] done!');
    }

    /*
     * Will take all of the data and reindex
     * Will run when reindex via command line
     */
    public function executeFull(){
        //Should take into account all placed orders in the system
        $this->logger->debug('[indexer catalog] asked to execute a full reindex');
        $this->catalog->execute();
        $this->logger->debug('[indexer catalog] done!');
    }


    /*
     * Works with a set of entity changed (may be massaction)
     */
    public function executeList(array $ids){
        //Works with a set of placed orders (mass actions and so on)
        $this->logger->debug('[indexer catalog] asked to execute reindex on specific entity: '.implode(',', $ids));
        $this->catalog->execute($ids);
        $this->logger->debug('[indexer catalog] done!');
    }


    /*
     * Works in runtime for a single entity using plugins
     */
    public function executeRow($id){
        //Works in runtime for a single order using plugins
        $this->logger->debug('[indexer catalog] asked to execute a reindex for single entity: '.$id);
        $this->catalog->execute([$id]);
        $this->logger->debug('[indexer catalog] done!');
    }
}
