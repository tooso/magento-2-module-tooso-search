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
     * @param TrackingInterface $tracking
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConfigInterface $config,
        TrackingInterface $tracking,
        LoggerInterface $logger
    )
    {
        $this->config = $config;
        $this->tracking = $tracking;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function doRequest($path, $httpMethod = \Tooso\SDK\Client::HTTP_METHOD_GET, array $params = array(), $timeout = null)
    {
        $client = $this->getClient();
        return $client->doRequest($path, $httpMethod, $params, $timeout);
    }

    /**
     * Get Client
     *
     * @return \Tooso\SDK\Client
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getClient()
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
