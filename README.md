# Tooso Search for Magento 2

[Tooso](http://tooso.ai) is a cloud-based, multi-language search tool for e-commerce.

This extension replaces the default search of Magento with a typo-tolerant, fast & relevant search experience backed by [Tooso](http://tooso.ai/Default.aspx).

## Description

This extension replaces the default Magento search engine with one based on Tooso API.
It provide the following features:

* Fulltext search for catalog products (currently advanced search is not supported)
* Scheduled indexing of catalog products (under development)
* Automatic typo correction (under development)
* Search keywords suggest (under development)

## Requirements

* PHP > 7.0
* [Composer](https://getcomposer.org/)
* Magento >= 2.2.6

## Installation Instructions

### Latest version

Install latest version using composer:
```bash
composer require bitbull/magento-2-tooso-search
```

### Specific version

Install a specific version using composer:
```bash
composer require bitbull/magento-2-tooso-search:1.0.0
```

### Development version

Set "minimum-stability" to "dev" and switch off the "prefer-stable" config:
```bash
composer config minimum-stability dev
composer config prefer-stable false
```

Install latest development version using composer:
```bash
composer require bitbull/magento-2-tooso-search:dev-develop
```

## Module Configuration

### Request your API KEY
Send an email to info@tooso.ai to request your APIKEY

### Set your API KEY: 
1. Under __API Configuration__
* Insert your API key into __API key__ field
* Insert __http://v{apiVersionWithNoDot}.api.tooso.ai__ into __API base url__ field. The current supported version is 1, so the placeholder {apiVersionWithNoDot} should be replaced by 1.
* __Send report__: __YES__ to send a report to Tooso when an API error occourred	
* __Debug mode__:  __Yes__ to enable more verbose logging for debug purpose
2. Save configuration


## Programmatically use Tooso service

If you would like to call Tooso service that currently are not supported with the plugin we suggest this configuration.

Include in your class a dependency from `Bitbull\Tooso\Api\Service\ClientInterface` and let DI system do the rest:
```php
<?php

use Tooso\SDK\ClientBuilder;
use Bitbull\Tooso\Api\Service\ConfigInterface;
use Bitbull\Tooso\Api\Service\TrackingInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;

class MyServiceClass
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
     * @var ClientBuilder
     */
    protected $clientBuilder;
    
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Search constructor.
     *
     * @param ConfigInterface $config
     * @param TrackingInterface $tracking
     * @param ClientBuilder $clientBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConfigInterface $config,
        TrackingInterface $tracking,
        ClientBuilder $clientBuilder,
        LoggerInterface $logger
    )
    {
        $this->config = $config;
        $this->tracking = $tracking;
        $this->clientBuilder = $clientBuilder;
        $this->logger = $logger;
    }

}

```
build the client instance using a `Tooso\SDK\ClientBuilder` instance
```php
<?php
    
    /**
     * Get Client
     *
     * @return \Tooso\SDK\Client
     */
    protected function getClient()
    {
        return $this->clientBuilder
            ->withApiKey($this->config->getApiKey())
            ->withApiVersion($this->config->getApiVersion())
            ->withApiBaseUrl($this->config->getApiBaseUrl())
            ->withLanguage($this->config->getLanguage())
            ->withStoreCode($this->config->getStoreCode())
            ->withAgent($this->tracking->getApiAgent()) //optional
            ->withLogger($this->logger) //optional
            ->build();
    }
``` 

this allow you to create a `Tooso\SDK\Client` instance to made any HTTP call to Tooso API with the pre-configured required parameters:
```php
<?php

    /**
     * Execute service
     */
    protected function execute()
    {
        $client = $this->getClient();
        $result = $client->doRequest('/path/to/service', \Tooso\SDK\Client::HTTP_METHOD_GET, [
            'param1' => 'value1',
            'param2' => 'value2'
        ]);
    }
```
access response data from the object of type `Tooso\SDK\Response` returned by `doRequest` method:
```php
<?php
$result = $client->doRequest('/path/to/service', \Tooso\SDK\Client::HTTP_METHOD_GET, [
    'param1' => 'value1',
    'param2' => 'value2'
]);
$responseData = $result->getResponse();
```
