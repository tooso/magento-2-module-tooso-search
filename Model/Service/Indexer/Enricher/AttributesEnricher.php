<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Service\Indexer\Enricher;

use Bitbull\Tooso\Api\Service\Indexer\EnricherInterface;
use Bitbull\Tooso\Api\Service\Config\IndexerConfigInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class AttributesEnricher implements EnricherInterface
{
        
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    
    /**
     * @var IndexerConfigInterface
     */
    protected $indexerConfig;

    /**
     * @var ProductCollectionFactory $productCollectionFactory
     * @var IndexerConfigInterface $indexerConfig
     */
    public function __construct(ProductRepositoryInterface $productRepository,  IndexerConfigInterface $indexerConfig)
    {
        $this->productRepository = $productRepository;
        $this->indexerConfig = $indexerConfig;
    }

    /**
     * @inheritdoc
     */
    public function execute($data)
    {
        $collection = $this->productRepository->getList();
        
        array_walk($data, function(&$d) {
           $d['name'] = 'Name' ;
           $d['description'] = 'This is a test description';
        });
        
        return $data;
    }
    
    /**
     * @inheritdoc
     */
    public function getEnrichedKeys()
    {
        return ['name', 'description'];
    }
    
}
