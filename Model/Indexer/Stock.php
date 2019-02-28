<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Indexer;

use Bitbull\Tooso\Api\Service\Indexer\StockInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;

class Stock implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    const INDEX_NAME = 'tooso_stock';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var StockInterface
     */
    protected $stock;

    /**
     * @param LoggerInterface $logger
     * @param StockInterface $stock
     */
    public function __construct(
        LoggerInterface $logger,
        StockInterface $stock
    )
    {
        $this->logger = $logger;
        $this->stock = $stock;
    }

    /*
     * Used by mview, allows process indexer in the "Update on schedule" mode
     */
    public function execute($ids){
        //Used by mview, allows you to process multiple placed orders in the "Update on schedule" mode
        $this->logger->debug('[indexer stock] asked to execute reindex for: '.implode(',', $ids));
        $this->stock->execute($ids);
        $this->logger->debug('[indexer stock] done!');
    }

    /*
     * Will take all of the data and reindex
     * Will run when reindex via command line
     */
    public function executeFull(){
        //Should take into account all placed orders in the system
        $this->logger->debug('[indexer stock] asked to execute a full reindex');
        $this->stock->execute();
        $this->logger->debug('[indexer stock] done!');
    }


    /*
     * Works with a set of entity changed (may be massaction)
     */
    public function executeList(array $ids){
        //Works with a set of placed orders (mass actions and so on)
        $this->logger->debug('[indexer stock] asked to execute reindex on specific entity: '.implode(',', $ids));
        $this->stock->execute($ids);
        $this->logger->debug('[indexer stock] done!');
    }


    /*
     * Works in runtime for a single entity using plugins
     */
    public function executeRow($id){
        //Works in runtime for a single order using plugins
        $this->logger->debug('[indexer stock] asked to execute a reindex for single entity: '.$id);
        $this->stock->execute([$id]);
        $this->logger->debug('[indexer stock] done!');
    }
}
