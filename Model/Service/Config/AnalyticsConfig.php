<?php

namespace Bitbull\Tooso\Model\Service\Config;

use Bitbull\Tooso\Api\Service\Config\AnalyticsConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class AnalyticsConfig implements AnalyticsConfigInterface
{
    const XML_PATH_ANALYTICS_TRACK_USERID = 'tooso/analytics/track_userid';
    const XML_PATH_ANALYTICS_COOKIE_DOMAIN = 'tooso/analytics/cookie_domain';

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
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
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
                $domainPart = explode('.', parse_url($url,  PHP_URL_HOST));
                $cookieDomain = '.'.$domainPart[count($domainPart) - 2].'.'.$domainPart[count($domainPart) - 1];
            }else{
                $cookieDomain = $default;
            }
        }
        return $cookieDomain;
    }

    /**
     * @inheritdoc
     */
    public function isUserIdTrackingEnable()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ANALYTICS_TRACK_USERID);
    }
}