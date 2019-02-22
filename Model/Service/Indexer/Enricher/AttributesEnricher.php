<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Service\Indexer\Enricher;

use Bitbull\Tooso\Api\Service\Indexer\EnricherInterface;
use Bitbull\Tooso\Api\Service\Config\IndexerConfigInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;

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
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    
    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;
    
    /**
     * @var FilterGroupBuilder
     */
    protected $filterGroupBuilder;

    /**
     * @var ProductCollectionFactory $productCollectionFactory
     * @var IndexerConfigInterface $indexerConfig
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,  
        IndexerConfigInterface $indexerConfig,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder 
    ) {
        $this->productRepository = $productRepository;
        $this->indexerConfig = $indexerConfig;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
    }

    /**
     * @inheritdoc
     */
    public function execute($data)
    {
        $ids = array_map(function($d) {
            return $d['id'];
        }, $data);
        
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchCriteria->setFilterGroups([
             $this->filterGroupBuilder
                ->addFilter(
                    $this->filterBuilder->setField('entity_id')
                      ->setValue($ids)
                      ->setConditionType('in')
                      ->create()
                )
                ->create()
        ]);
        
        //TODO: select only attributes present in configurations
        
        $productRepository = $this->productRepository->getList($searchCriteria);
        $products = $productRepository->getItems();
        
        $attributes = $this->indexerConfig->getAttributesWithoutCustoms();
        
        array_walk($products, function($product) use ($productRepository, $ids, &$data) {
           $dataIndex = array_search($product->getId(), $ids);
           if ($dataIndex === -1) {
               return; // this shouldn't happen
           }
           
           //TODO: dinamically load attributes based on configurations
           
           $data[$dataIndex]['name'] = $product->getName();
           $data[$dataIndex]['description'] = $product->getDescription();
        });
        
        return $data;
    }
    
    /**
     * @inheritdoc
     */
    public function getEnrichedKeys()
    {
        return ['name', 'description']; //TODO: these values are not static, get them from config
    }
    
}
