<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Service\Indexer\Enricher;

use Bitbull\Tooso\Api\Service\Indexer\EnricherInterface;
use Bitbull\Tooso\Api\Service\Config\IndexerConfigInterface;

class CategoriesEnricher implements EnricherInterface
{
    
    /**
     * @var IndexerConfigInterface
     */
    protected $indexerConfig;

    /**
     * @var IndexerConfigInterface $indexerConfig
     */
    public function __construct(IndexerConfigInterface $indexerConfig)
    {
        $this->indexerConfig = $indexerConfig;
    }
    
    /**
     * @inheritdoc
     */
    public function execute($data)
    {
        //TODO: implement me
        
        array_walk($data, function(&$d) {
            $d['categories'] = [
                [
                    'sku' => 'var1',
                    'name' => 'test'
                ]
            ];
        });
        
        return $data;
    }
    
    /**
     * @inheritdoc
     */
    public function getEnrichedKeys()
    {
        return ['categories'];
    }
}
