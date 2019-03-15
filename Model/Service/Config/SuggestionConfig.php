<?php declare(strict_types=1);

namespace Bitbull\Tooso\Model\Service\Config;

use Bitbull\Tooso\Api\Service\Config\SuggestionConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class SuggestionConfig implements SuggestionConfigInterface
{
    const XML_PATH_SUGGEST_LIBRARY_INCLUDE = 'tooso/suggestion/include_library';
    const XML_PATH_SUGGEST_LIBRARY_ENDPOINT = 'tooso/suggestion/library_endpoint';
    const XML_PATH_SUGGEST_INPUT_SELECTOR = 'tooso/suggestion/input_selector';
    const XML_PATH_SUGGEST_API_KEY = 'tooso/server/api_key'; //NOTE: at this this suggestion API key is the same as search API key

    const XML_PATH_SUGGEST_BUCKETS = 'tooso/suggestion/buckets';
    const XML_PATH_SUGGEST_LIMIT = 'tooso/suggestion/limit';
    const XML_PATH_SUGGEST_GROUPBY = 'tooso/suggestion/groupby';
    const XML_PATH_SUGGEST_NOCACHE = 'tooso/suggestion/nocache';
    const XML_PATH_SUGGEST_ONSELECT_BEHAVIOUR = 'tooso/suggestion/onselect_behaviour';
    const XML_PATH_SUGGEST_ONSELECT_CALLBACK = 'tooso/suggestion/onselect_callback';
    const XML_PATH_SUGGEST_MINCHAR = 'tooso/suggestion/minchars';
    const XML_PATH_SUGGEST_WIDTH = 'tooso/suggestion/width';
    const XML_PATH_SUGGEST_WIDTH_CUSTOM = 'tooso/suggestion/width_custom';
    const XML_PATH_SUGGEST_ZINDEX = 'tooso/suggestion/zindex';

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
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function getLibraryEndpoint()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SUGGEST_LIBRARY_ENDPOINT);
    }

    /**
     * @inheritdoc
     */
    public function getInputSelector()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SUGGEST_INPUT_SELECTOR);
    }

    /**
     * @inheritdoc
     */
    public function getInitParams()
    {
        $data = [
            'language' => 'en_us',
            'groupBy' => $this->scopeConfig->isSetFlag(self::XML_PATH_SUGGEST_GROUPBY),
            'noCache' => $this->scopeConfig->isSetFlag(self::XML_PATH_SUGGEST_NOCACHE),
            'autocomplete' => [
                'width' => $this->getWidthValue(),
            ]
        ];

        $locale = $this->storeManager->getStore()->getLocaleCode();
        if($locale !== null){
            $data['language'] = strtolower($locale);
        }

        $apiKey = $this->scopeConfig->getValue(self::XML_PATH_SUGGEST_API_KEY);
        if($apiKey !== null){
            $data['apiKey'] = $apiKey;
        }

        $buckets = $this->scopeConfig->getValue(self::XML_PATH_SUGGEST_BUCKETS);
        if($buckets !== null){
            $data['buckets'] = $buckets;
        }

        $limit = $this->scopeConfig->getValue(self::XML_PATH_SUGGEST_LIMIT);
        if($limit !== null){
            $data['limit'] = $limit;
        }

        $minChars = $this->scopeConfig->getValue(self::XML_PATH_SUGGEST_MINCHAR);
        if($minChars !== null){
            $data['autocomplete']['minChars'] = $minChars;
        }

        $zIndex = $this->scopeConfig->getValue(self::XML_PATH_SUGGEST_ZINDEX);
        if($zIndex !== null){
            $data['autocomplete']['zIndex'] = $zIndex;
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getOnSelectCallbackValue()
    {
        $behaviour = $this->scopeConfig->getValue(self::XML_PATH_SUGGEST_ONSELECT_BEHAVIOUR);
        switch ($behaviour) {
            case 'submit':
                return 'function() { this.form.submit(); }';
            case 'custom':
                return $this->scopeConfig->getValue(self::XML_PATH_SUGGEST_ONSELECT_CALLBACK);
            case 'nothing':
                return 'function() { }';
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getWidthValue()
    {
        $width = $this->scopeConfig->getValue(self::XML_PATH_SUGGEST_WIDTH);
        if($width === 'custom'){
            return $this->scopeConfig->getValue(self::XML_PATH_SUGGEST_WIDTH_CUSTOM);
        }
        return $width;
    }

    /**
     * @inheritdoc
     */
    public function isLibraryIncluded()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SUGGEST_LIBRARY_INCLUDE);
    }
}
