<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Indexer;

use Bitbull\Tooso\Api\Service\LoggerInterface;
use Bitbull\Tooso\Model\Indexer\Operation\OperationFactory;
use Bitbull\Tooso\Model\Indexer\Decorator\CatalogDecoratorFactory;
use Bitbull\Tooso\Model\Indexer\Decorator\CategoriesDecoratorFactory;
use Bitbull\Tooso\Model\Indexer\Decorator\VariantsDecoratorFactory;

class Catalog implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;
    
    /**
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
    }
    
    /*
     * Used by mview, allows process indexer in the "Update on schedule" mode
     */
    public function execute($ids){
        //Used by mview, allows you to process multiple placed orders in the "Update on schedule" mode
        $this->logger->debug('[indexer catalog] asked to execute reindex for: '.implode(',', $ids));
    }

    /*
     * Will take all of the data and reindex
     * Will run when reindex via command line
     */
    public function executeFull(){
        //Should take into account all placed orders in the system
        $this->logger->debug('[indexer catalog] asked to execute a full reindex');
    }


    /*
     * Works with a set of entity changed (may be massaction)
     */
    public function executeList(array $ids){
        //Works with a set of placed orders (mass actions and so on)
        $this->logger->debug('[indexer catalog] asked to execute reindex on specific entity: '.implode(',', $ids));
    }


    /*
     * Works in runtime for a single entity using plugins
     */
    public function executeRow($id){
        //Works in runtime for a single order using plugins
        $this->logger->debug('[indexer catalog] asked to execute a reindex for single entity: '.$id);
    }
}
