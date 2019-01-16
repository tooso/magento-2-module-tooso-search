<?php

namespace Bitbull\Tooso\Model\Service;

use Bitbull\Tooso\Api\Service\ClientInterface;
use Bitbull\Tooso\Api\Service\ConfigInterface;
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
     * Client constructor.
     *
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
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
                ->withAgent('New super-cool Magento2 module')
                ->build();
    }
}