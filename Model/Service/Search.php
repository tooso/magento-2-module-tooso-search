<?php declare(strict_types=1);

namespace Bitbull\Tooso\Model\Service;

use Bitbull\Tooso\Api\Service\ClientInterface;
use Bitbull\Tooso\Api\Service\Config\SearchConfigInterface;
use Bitbull\Tooso\Api\Service\SearchInterface;
use Bitbull\Tooso\Api\Service\TrackingInterface;
use Tooso\SDK\Exception;

class Search implements SearchInterface
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var SearchConfigInterface
     */
    protected $config;

    /**
     * @var TrackingInterface
     */
    protected $tracking;

    /**
     * Search constructor.
     *
     * @param ClientInterface $client
     * @param SearchConfigInterface $config
     * @param TrackingInterface $tracking
     */
    public function __construct(ClientInterface $client, SearchConfigInterface $config, TrackingInterface $tracking)
    {
        $this->client = $client;
        $this->config = $config;
        $this->tracking = $tracking;
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function execute($query, $typoCorrection = true)
    {
        return $this->client->build()->search(
            $query,
            $typoCorrection,
            $this->tracking->getProfilingParams(),
            $this->config->isEnriched()
        );
    }
}
