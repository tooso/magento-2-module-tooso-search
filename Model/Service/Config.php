<?php declare(strict_types=1);

namespace Bitbull\Tooso\Model\Service;

use Bitbull\Tooso\Api\Service\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config implements ConfigInterface
{
    const XML_PATH_GENERAL_LOCALE_CODE = 'general/locale/code';
    const XML_PATH_SEARCH_ACTIVE = 'tooso/active/frontend';
    const XML_PATH_TRACKING_ACTIVE = 'tooso/active/tracking';
    const XML_PATH_SUGGESTION_ACTIVE = 'tooso/active/suggestion';
    const XML_PATH_SPEECH_TO_TEXT_ACTIVE = 'tooso/active/speech_to_text';
    const XML_PATH_API_KEY = 'tooso/server/api_key';
    const XML_PATH_API_VERSION = 'tooso/server/api_version';
    const XML_PATH_API_BASE_URL = 'tooso/server/api_base_url';
    const XML_PATH_DEBUG_MODE = 'tooso/server/debug_mode';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(ScopeConfigInterface $scopeConfig, StoreManagerInterface $storeManager)
    {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function getLanguage()
    {
        $currentLocaleCode = $this->storeManager->getStore()->getLocaleCode();
        if ($currentLocaleCode !== null) {
            return $currentLocaleCode;
        }

        return $this->scopeConfig->getValue(self::XML_PATH_GENERAL_LOCALE_CODE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
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
        return $this->scopeConfig->getValue(self::XML_PATH_API_KEY, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStoreCode());
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

    /**
     * @inheritdoc
     */
    public function isTrackingEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_TRACKING_ACTIVE);
    }

    /**
     * @inheritdoc
     */
    public function isSuggestionEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SUGGESTION_ACTIVE);
    }

    /**
     * @inheritdoc
     */
    public function isSdkEnabled()
    {
        return $this->isSpeechToTextEnabled(); // Insert here more service that require SDK library
    }

    /**
     * @inheritdoc
     */
    public function isSpeechToTextEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SPEECH_TO_TEXT_ACTIVE);
    }
}
