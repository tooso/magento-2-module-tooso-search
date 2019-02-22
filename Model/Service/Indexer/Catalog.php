<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Service\Indexer;

use Bitbull\Tooso\Api\Service\Indexer\EnricherInterface;
use Bitbull\Tooso\Api\Service\Indexer\CatalogInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;


class Catalog implements CatalogInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger; 
    
    /**
     * @var array
     */
    protected $enrichers;

    /**
     * @var LoggerInterface $logger
     * @var EnricherInterface[] $enrichers
     */
    public function __construct(LoggerInterface $logger, array $enrichers)
    {
        $this->logger = $logger;
        $this->enrichers = $enrichers;
    }
    
    /**
     * @inheritdoc
     */
    public function execute($ids = null)
    {
        // Init data
        
        $data = array_map(function($elem){
            return [
                'id' => $elem
            ];
        }, $ids);
        
        // Run enrichers
        
        array_walk($this->enrichers, function ($enricher) use (&$data) {
            
            if (sizeof($data) === 0) {
                throw new Exception('No data provided to enricher');
            }
            
            $currentKeys = array_keys($data[0]);
            $collisionKeys = array_intersect($currentKeys, $enricher->getEnrichedKeys());
            if (sizeof($collisionKeys) !== 0) {
                throw new Exception('An other enricher did the same job, collision keys are: '.implode(',', $collisionKeys));
            }
            
            $data = $enricher->execute($data);
            
        });
        
        // Now $data is enriched
        
    }
}