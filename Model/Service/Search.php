<?php

namespace Bitbull\Tooso\Model\Service;

use Bitbull\Tooso\Api\Service\Config\SearchConfigInterface;
use Bitbull\Tooso\Api\Service\ConfigInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;
use Bitbull\Tooso\Api\Service\SearchInterface;
use Bitbull\Tooso\Api\Service\TrackingInterface;
use Tooso\SDK\Exception;
use Tooso\SDK\ClientBuilder;

class Search implements SearchInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var SearchConfigInterface
     */
    protected $searchConfig;

    /**
     * @var TrackingInterface
     */
    protected $tracking;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ClientBuilder
     */
    protected $clientBuilder;

    /**
     * Search constructor.
     *
     * @param ConfigInterface $config
     * @param SearchConfigInterface $searchConfig
     * @param TrackingInterface $tracking
     * @param LoggerInterface $logger
     */
    public function __construct(ConfigInterface $config, SearchConfigInterface $searchConfig, TrackingInterface $tracking, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->searchConfig = $searchConfig;
        $this->tracking = $tracking;
        $this->logger = $logger;
        $this->clientBuilder = new ClientBuilder();
    }

    /**
     * @inheritdoc
     * @throws Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute($query, $typoCorrection = true)
    {
        return $this->_getClient()->search(
            $query,
            $typoCorrection,
            $this->tracking->getProfilingParams(),
            $this->searchConfig->isEnriched()
        );
    }

    /**
     * Get Client
     *
     * @return \Tooso\SDK\Client
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function _getClient()
    {
        return $this->clientBuilder
            ->withApiKey($this->config->getApiKey())
            ->withApiVersion($this->config->getApiVersion())
            ->withApiBaseUrl($this->config->getApiBaseUrl())
            ->withLanguage($this->config->getLanguage())
            ->withStoreCode($this->config->getStoreCode())
            ->withAgent($this->tracking->getApiAgent())
            ->withLogger($this->logger)
            ->build();
    }
}