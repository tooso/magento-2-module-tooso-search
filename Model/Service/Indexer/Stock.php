<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Service\Indexer;

use Bitbull\Tooso\Api\Service\Indexer\StockInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;
use Bitbull\Tooso\Api\Service\Config\IndexerConfigInterface;
use Bitbull\Tooso\Model\Service\Indexer\Db\StockIndexFlat;
use Bitbull\Tooso\Model\Service\Indexer\Enricher\StockEnricher;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

class Stock implements StockInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var StockIndexFlat
     */
    protected $stockIndexFlat;

    /**
     * @var IndexerConfigInterface
     */
    protected $indexerConfig;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var StockEnricher
     */
    protected $stockEnricher;

    /**
     * @var LoggerInterface $logger
     * @var IndexerConfigInterface $indexerConfig
     * @var StockIndexFlat $stockIndexFlat
     * @var ProductCollectionFactory $productCollectionFactory
     * @var StockEnricher $stockEnricher
     */
    public function __construct(
        LoggerInterface $logger,
        IndexerConfigInterface $indexerConfig,
        StockIndexFlat $stockIndexFlat,
        ProductCollectionFactory $productCollectionFactory,
        StockEnricher $stockEnricher
    )
    {
        $this->logger = $logger;
        $this->indexerConfig = $indexerConfig;
        $this->stockIndexFlat = $stockIndexFlat;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->stockEnricher = $stockEnricher;
    }

    /**
     * @inheritdoc
     */
    public function execute($ids = null)
    {
        if ($ids === null) {
            $ids = [];
            $productsCollection = $this->productCollectionFactory->create()
                ->addAttributeToSelect('entity_id')
                ->addFieldToFilter('visibility', 4);

            //TODO: array_map? toArray()? need something more "clean"
            foreach ($productsCollection as $product) {
                $ids[] = $product->getId();
            }
        }

        $stores = $this->indexerConfig->getStores();

        foreach ($stores as $storeId) {
            // Init data

            $data = array_map(function($elem){
                return [
                    'id' => $elem
                ];
            }, $ids);

            // Run enricher

            $data = $this->stockEnricher->execute($data);

            // Now $data is enriched, store it

            $this->stockIndexFlat->storeData($data, $storeId);
        }


    }
}
