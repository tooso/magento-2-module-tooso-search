<?php declare(strict_types=1);

namespace Bitbull\Tooso\Model\Service;

use Bitbull\Tooso\Api\Service\ClientInterface;
use Bitbull\Tooso\Api\Service\ConfigInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;
use Bitbull\Tooso\Api\Service\TrackingInterface;
use Tooso\SDK\ClientBuilder;

class Client implements ClientInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var ClientBuilder
     */
    protected $clientBuilder;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var LoggerInterface
     */
    protected $tracking;

    /**
     * Client constructor.
     *
     * @param ConfigInterface $config
     * @param TrackingInterface $tracking
     * @param LoggerInterface $logger
     */
    public function __construct(ConfigInterface $config, TrackingInterface $tracking, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->tracking = $tracking;
        $this->clientBuilder = new ClientBuilder();
    }

    /**
     * @inheritdoc
     */
    public function build()
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
