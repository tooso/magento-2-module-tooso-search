<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Service\Indexer;

use Bitbull\Tooso\Api\Service\Indexer\EnricherInterface;
use Bitbull\Tooso\Api\Service\Indexer\CatalogInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;
use Bitbull\Tooso\Api\Service\Config\IndexerConfigInterface;

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
     * @var IndexerConfigInterface
     */
    protected $indexerConfig;

    /**
     * @var LoggerInterface $logger
     * @var IndexerConfigInterface $indexerConfig
     * @var EnricherInterface[] $enrichers
     */
    public function __construct(LoggerInterface $logger, IndexerConfigInterface $indexerConfig, array $enrichers)
    {
        $this->logger = $logger;
        $this->indexerConfig = $indexerConfig;
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

            $data = $enricher->execute($data);

        });

        // Now $data is enriched



    }
}
