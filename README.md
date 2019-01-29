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