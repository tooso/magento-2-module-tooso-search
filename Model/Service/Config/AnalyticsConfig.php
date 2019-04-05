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
    const XML_PATH_ANALYTICS_PRODUCT_SELECTOR = 'tooso/analytics/product_link_selector';
    const XML_PATH_ANALYTICS_PRODUCT_CONTAINER_SELECTOR = 'tooso/analytics/product_container_selector';
    const XML_PATH_ANALYTICS_PRODUCT_ATTRIBUTE = 'tooso/analytics/product_link_attribute';
    const XML_PATH_ANALYTICS_SEARCH_ID_ATTRIBUTE = 'tooso/analytics/search_id_attribute';
    const XML_PATH_ANALYTICS_PAGINATION_TYPE = 'tooso/analytics/pagination_type';
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
    public function getProductLinkSelector()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ANALYTICS_PRODUCT_SELECTOR);
    }

    /**
     * @inheritdoc
     */
    public function getProductContainerSelector()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ANALYTICS_PRODUCT_CONTAINER_SELECTOR);
    }

    /**
     * @inheritdoc
     */
    public function getProductAttributeName()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ANALYTICS_PRODUCT_ATTRIBUTE);
    }

    /**
     * @inheritdoc
     */
    public function getSearchIdAttribute()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ANALYTICS_SEARCH_ID_ATTRIBUTE);
    }

    /**
     * @inheritdoc
     */
    public function getPaginationType()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ANALYTICS_PAGINATION_TYPE);
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
