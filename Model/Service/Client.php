<?php

namespace Bitbull\Tooso\Service;

use Bitbull\Tooso\Api\Service\ClientInterface;
use Bitbull\Tooso\Api\Service\ConfigInterface;
use \Tooso\SDK\ClientInterface as SDKClientInterface;
use \Tooso\SDK\Client as SDKClient;

class Client implements ClientInterface
{
    /**
     * @var SDKClientInterface
     */
    protected $client;

    /**
     * Client constructor.
     *
     * @param ConfigInterface $config
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function __construct(ConfigInterface $config)
    {
        $this->client = new SDKClient(
            $config->getApiKey(),
            $config->getApiVersion(),
            $config->getApiBaseUrl(),
            $config->getLanguage(),
            $config->getStoreCode()
        );
    }

    /**
     * @inheritdoc
     */
    public function getClient()
    {
        return $this->client;
    }
}