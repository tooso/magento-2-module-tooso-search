<?php declare(strict_types=1);

namespace Bitbull\Tooso\Api\Service\Indexer;

interface EnricherInterface
{
    /**
     * @var array $data
     * 
     * @return array
     */
    public function execute($data);
    
    /**
     * @return array
     */
    public function getEnrichedKeys();
}