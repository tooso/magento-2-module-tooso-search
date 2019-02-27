<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Service\Indexer;

use Bitbull\Tooso\Api\Service\Indexer\EnricherInterface;
use Bitbull\Tooso\Api\Service\Indexer\CatalogInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;
use Bitbull\Tooso\Api\Service\Config\IndexerConfigInterface;
use Bitbull\Tooso\Model\Service\Indexer\Db\CatalogIndexFlat;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

class Catalog implements CatalogInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CatalogIndexFlat
     */
    protected $catalogIndexFlat;

    /**
     * @var array
     */
    protected $enrichers;

    /**
     * @var IndexerConfigInterface
     */
    protected $indexerConfig;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var LoggerInterface $logger
     * @var IndexerConfigInterface $indexerConfig
     * @var CatalogIndexFlat $catalogIndexFlat
     * @var ProductCollectionFactory $productCollectionFactory
     * @var EnricherInterface[] $enrichers
     */
    public function __construct(
        LoggerInterface $logger,
        IndexerConfigInterface $indexerConfig,
        CatalogIndexFlat $catalogIndexFlat,
        ProductCollectionFactory $productCollectionFactory,
        array $enrichers
    )
    {
        $this->logger = $logger;
        $this->indexerConfig = $indexerConfig;
        $this->catalogIndexFlat = $catalogIndexFlat;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->enrichers = $enrichers;
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

            // Run enrichers

            $attributes = $this->indexerConfig->getAttributes();
            array_walk($this->enrichers, function ($enricher) use (&$data, $attributes) {

                if (sizeof($data) === 0) {
                    throw new \UnexpectedValueException('No data provided to enricher');  //TODO: use a proper exception
                }

                if (sizeof(array_intersect($enricher->getEnrichedKeys(), $attributes)) === 0) {
                    return;
                }

                $currentKeys = array_keys($data[0]);
                $collisionKeys = array_intersect($currentKeys, $enricher->getEnrichedKeys());
                if (sizeof($collisionKeys) !== 0) {
                    throw new \UnexpectedValueException('An other enricher did the same job, collision keys are: '.implode(',', $collisionKeys)); //TODO: use a proper exception
                }

                $data = $enricher->execute($data); // TODO: pass store id

            });

            // Now $data is enriched, store it

            $this->catalogIndexFlat->storeData($data, $storeId);
        }


    }
}
