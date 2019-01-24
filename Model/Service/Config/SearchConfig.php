<?php declare(strict_types=1);

namespace Bitbull\Tooso\Model\Service\Config;

use Bitbull\Tooso\Api\Service\Config\SearchConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class SearchConfig implements SearchConfigInterface
{
    const XML_PATH_SEARCH_FALLBACK_ENABLE = 'tooso/search/fallback_enable';
    const XML_PATH_SEARCH_RESPONSE_TYPE = 'tooso/search/response_type';
    const XML_PATH_SEARCH_DEFAULT_LIMIT = 'tooso/search/default_limit';
    const XML_PATH_SEARCH_FILTER_EXCLUSION_PARAMS = 'tooso/search/exclude_params';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultLimit()
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_SEARCH_DEFAULT_LIMIT);
        if ($value !== null) {
            $value = (int) $value;
        }
        return $value;
    }

    /**
     * @inheritdoc
     */
    public function getParamFilterExclusion()
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_SEARCH_DEFAULT_LIMIT);
        if ($value !== null) {
            $value = (int) $value;
        }
        return $value;
    }

    /**
     * @inheritdoc
     */
    public function isEnriched()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SEARCH_RESPONSE_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function isFallbackEnable()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SEARCH_FALLBACK_ENABLE);
    }
}
