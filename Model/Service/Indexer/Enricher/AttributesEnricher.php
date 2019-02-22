<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Service\Indexer\Enricher;

use Bitbull\Tooso\Api\Service\Indexer\EnricherInterface;

class AttributesEnricher implements EnricherInterface
{

    /**
     * @inheritdoc
     */
    public function execute($data)
    {
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
