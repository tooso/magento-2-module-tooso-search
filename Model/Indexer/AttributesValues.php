<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Indexer;

use Bitbull\Tooso\Api\Service\Indexer\AttributesValuesInterface;
use Bitbull\Tooso\Api\Service\Indexer\CatalogInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;

class AttributesValues implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    const INDEX_NAME = 'tooso_attributes_values';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var AttributesValuesInterface
     */
    protected $attributesValues;

    /**
     * @param LoggerInterface $logger
     * @param AttributesValuesInterface $attributesValues
     */
    public function __construct(
        LoggerInterface $logger,
        AttributesValuesInterface $attributesValues
    )
    {
        $this->logger = $logger;
        $this->attributesValues = $attributesValues;
    }

    /*
     * Used by mview, allows process indexer in the "Update on schedule" mode
     */
    public function execute($ids){
        //Used by mview, allows you to process multiple placed orders in the "Update on schedule" mode
        $this->logger->debug('[indexer attributesValues] asked to execute reindex for: '.implode(',', $ids));
        $this->attributesValues->execute($ids);
        $this->logger->debug('[indexer attributesValues] done!');
    }

    /*
     * Will take all of the data and reindex
     * Will run when reindex via command line
     */
    public function executeFull(){
        //Should take into account all placed orders in the system
        $this->logger->debug('[indexer attributesValues] asked to execute a full reindex');
        $this->attributesValues->execute();
        $this->logger->debug('[indexer attributesValues] done!');
    }


    /*
     * Works with a set of entity changed (may be massaction)
     */
    public function executeList(array $ids){
        //Works with a set of placed orders (mass actions and so on)
        $this->logger->debug('[indexer attributesValues] asked to execute reindex on specific entity: '.implode(',', $ids));
        $this->attributesValues->execute($ids);
        $this->logger->debug('[indexer attributesValues] done!');
    }


    /*
     * Works in runtime for a single entity using plugins
     */
    public function executeRow($id){
        //Works in runtime for a single order using plugins
        $this->logger->debug('[indexer attributesValues] asked to execute a reindex for single entity: '.$id);
        $this->attributesValues->execute([$id]);
        $this->logger->debug('[indexer attributesValues] done!');
    }
}
