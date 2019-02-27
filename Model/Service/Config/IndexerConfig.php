<?php declare(strict_types=1);

namespace Bitbull\Tooso\Model\Service\Config;

use Bitbull\Tooso\Api\Service\Config\IndexerConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Bitbull\Tooso\Model\Adminhtml\System\Config\Source\Attributes as SourceAttributes;

class IndexerConfig implements IndexerConfigInterface
{
    const XML_PATH_INDEXER_STORES = 'tooso/indexer/stores_to_index';
    const XML_PATH_INDEXER_ATTRIBUTES = 'tooso/indexer/attributes_to_index';
    const XML_PATH_INDEXER_ATTRIBUTES_SIMPLE = 'tooso/indexer/attributes_simple_to_index';
    const XML_PATH_INDEXER_ACCESS_KEY = 'tooso/indexer/access_key';
    const XML_PATH_INDEXER_SECRET_KEY = 'tooso/indexer/secret_key';
    const XML_PATH_INDEXER_BUCKET_NAME = 'tooso/indexer/bucket';
    const XML_PATH_INDEXER_BUCKET_PATH = 'tooso/indexer/path';

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

    /**
     * @inheritdoc
     */
    public function getSimpleAttributes()
    {
        $value = $this->scopeConfig->getValue(self::XML_PATH_INDEXER_ATTRIBUTES_SIMPLE);
        if ($value === null) {
            return [];
        }
        return explode(',', $value);
    }

    /**
     * @inheritdoc
     */
    public function getAwsAccessKey()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_INDEXER_ACCESS_KEY);
    }

    /**
     * @inheritdoc
     */
    public function getAwsSecretKey()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_INDEXER_SECRET_KEY);
    }

    /**
     * @inheritdoc
     */
    public function getAwsBucketName()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_INDEXER_BUCKET_NAME);
    }

    /**
     * @inheritdoc
     */
    public function getAwsBucketPath()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_INDEXER_BUCKET_PATH);
    }

}
