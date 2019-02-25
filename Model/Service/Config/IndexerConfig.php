<?php declare(strict_types=1);

namespace Bitbull\Tooso\Model\Service\Config;

use Bitbull\Tooso\Api\Service\Config\IndexerConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Bitbull\Tooso\Model\Adminhtml\System\Config\Source\Attributes as SourceAttributes;

class IndexerConfig implements IndexerConfigInterface
{
    const XML_PATH_INDEXER_STORES = 'tooso/indexer/stores_to_index';
    const XML_PATH_INDEXER_ATTRIBUTES = 'tooso/indexer/attributes_to_index';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var SourceAttributes
     */
    protected $sourceAttributes;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param SourceAttributes $sourceAttributes
     */
    public function __construct(ScopeConfigInterface $scopeConfig, SourceAttributes $sourceAttributes)
    {
        $this->scopeConfig = $scopeConfig;
        $this->sourceAttributes = $sourceAttributes;
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
            return [];
        }
        return explode(',', $value);
    }

    /**
     * @inheritdoc
     */
    public function getAttributesWithoutCustoms()
    {
        $attributes = $this->getAttributes();
        $customAttributes = $this->sourceAttributes->getCustomAttributes();
        return array_diff($attributes, $customAttributes);
    }

}
