<?php declare(strict_types=1);

namespace Bitbull\Tooso\Model\Service\Config;

use Bitbull\Tooso\Api\Service\Config\AnalyticsConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Zend\Uri\Http;

class AnalyticsConfig implements AnalyticsConfigInterface
{
    const XML_PATH_ANALYTICS_INCLUDE_LIBRARY = 'tooso/analytics/include_library';
    const XML_PATH_ANALYTICS_LIBRARY_ENDPOINT = 'tooso/analytics/library_endpoint';
    const XML_PATH_ANALYTICS_API_ENDPOINT = 'tooso/analytics/api_endpoint';
    const XML_PATH_ANALYTICS_API_VERSION = 'tooso/analytics/api_version';
    const XML_PATH_ANALYTICS_KEY = 'tooso/analytics/key';
    const XML_PATH_ANALYTICS_DEBUG_MODE = 'tooso/analytics/debug_mode';
    const XML_PATH_ANALYTICS_COOKIE_DOMAIN = 'tooso/analytics/cookie_domain';
    const XML_PATH_ANALYTICS_TRACK_USERID = 'tooso/analytics/track_userid';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Http
     */
    private $httpUriHandler;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Http $httpUriHandler
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Http $httpUriHandler
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->httpUriHandler = $httpUriHandler;
    }

    /**
     * @inheritdoc
     */
    public function getCookieDomain($default = null)
    {
        $cookieDomain = $this->scopeConfig->getValue(self::XML_PATH_ANALYTICS_COOKIE_DOMAIN);
        if ($cookieDomain === null || trim($cookieDomain) === '') {
            if ($default === null) {
                $url = $this->storeManager->getStore()->getBaseUrl();
                $domainPart = explode('.', $this->httpUriHandler->parse($url)->getHost());
                $cookieDomain = '.'.$domainPart[count($domainPart) - 2].'.'.$domainPart[count($domainPart) - 1];
            } else {
                $cookieDomain = $default;
            }
        }
        return $cookieDomain;
    }

    /**
     * @inheritdoc
     */
    public function getLibraryEndpoint()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ANALYTICS_LIBRARY_ENDPOINT);
    }

    /**
     * @inheritdoc
     */
    public function getAPIEndpoint()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ANALYTICS_API_ENDPOINT);
    }

    /**
     * @inheritdoc
     */
    public function getAPIVersion()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ANALYTICS_API_VERSION);
    }

    /**
     * @inheritdoc
     */
    public function getKey()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ANALYTICS_KEY);
    }

    /**
     * @inheritdoc
     */
    public function isLibraryIncluded()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ANALYTICS_INCLUDE_LIBRARY);
    }

    /**
     * @inheritdoc
     */
    public function isDebugMode()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ANALYTICS_DEBUG_MODE);
    }

    /**
     * @inheritdoc
     */
    public function isUserIdTrackingEnable()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ANALYTICS_TRACK_USERID);
    }
}
