<?php

namespace Bitbull\Tooso\Service;

use Bitbull\Tooso\Api\Service\SearchConfigInterface;

class SearchConfig implements SearchConfigInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Config constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function getApiKey()
    {

    }

    /**
     * @inheritdoc
     */
    public function getApiVersion()
    {

    }

    /**
     * @inheritdoc
     */
    public function getApiBaseUrl()
    {

    }

}