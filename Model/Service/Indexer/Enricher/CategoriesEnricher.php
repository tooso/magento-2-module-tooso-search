<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Service\Indexer\Enricher;

use Bitbull\Tooso\Api\Service\Indexer\EnricherInterface;

class CategoriesEnricher implements EnricherInterface
{
    
    /**
     * @inheritdoc
     */
    public function execute($data)
    {
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
