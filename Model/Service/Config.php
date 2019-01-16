<?php

namespace Bitbull\Tooso\Model\Service;

use Bitbull\Tooso\Api\Service\ConfigInterface;

class Config implements ConfigInterface
{
    const XML_PATH_SEARCH_ACTIVE = 'tooso/active/frontend';

    const XML_PATH_API_KEY = 'tooso/server/api_key';
    const XML_PATH_API_VERSION = 'tooso/server/api_version';
    const XML_PATH_API_BASE_URL = 'tooso/server/api_base_url';
    const XML_PATH_DEBUG_MODE = 'tooso/server/debug_mode';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Config constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function getLanguage()
    {
        return $this->storeManager->getStore()->getLocaleCode();
    }

    /**
     * @inheritdoc
     */
    public function getStoreCode()
    {
        return $this->storeManager->getStore()->getCode();
    }

    /**
     * @inheritdoc
     */
    public function getApiKey()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_API_KEY);
    }

    /**
     * @inheritdoc
     */
    public function getApiVersion()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_API_VERSION);
    }

    /**
     * @inheritdoc
     */
    public function getApiBaseUrl()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_API_BASE_URL);
    }

    /**
     * @inheritdoc
     */
    public function isDebugModeEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_DEBUG_MODE);
    }

    /**
     * @inheritdoc
     */
    public function isSearchEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SEARCH_ACTIVE);
    }
}