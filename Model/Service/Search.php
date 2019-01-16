<?php

namespace Bitbull\Tooso\Model\Service;

use Bitbull\Tooso\Api\Service\ClientInterface;
use Bitbull\Tooso\Api\Service\Config\SearchConfigInterface;
use Bitbull\Tooso\Api\Service\SearchInterface;
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
     * Search constructor.
     *
     * @param ClientInterface $client
     * @param SearchConfigInterface $config
     */
    public function __construct(ClientInterface $client, SearchConfigInterface $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    public function execute($query, $typoCorrection = true)
    {
        return $this->client->build()->search($query, $typoCorrection, [], $this->config->isEnriched());
    }
}