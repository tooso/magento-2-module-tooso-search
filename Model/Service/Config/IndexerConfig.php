<?php declare(strict_types=1);

namespace Bitbull\Tooso\Model\Service\Config;

use Bitbull\Tooso\Api\Service\Config\IndexerConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class IndexerConfig implements IndexerConfigInterface
{
    const XML_PATH_INDEXER_STORES = 'tooso/indexer/stores_to_index';
    const XML_PATH_INDEXER_ATTRIBUTES = 'tooso/indexer/attributes_to_index';

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
    public function getStores()
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_INDEXER_STORES);
        if ($value === null) {
            $value = [];
        }
        return explode(',', $value);
    }
    
    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_INDEXER_ATTRIBUTES);
        if ($value === null) {
            $value = [];
        }
        return explode(',', $value);
    }

}
