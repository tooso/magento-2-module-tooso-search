<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Service\Indexer;

use Bitbull\Tooso\Api\Service\Indexer\AttributesValuesInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;
use Bitbull\Tooso\Api\Service\Config\IndexerConfigInterface;
use Bitbull\Tooso\Model\Service\Indexer\Db\AttributesValuesIndexFlat;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;

class AttributesValues implements AttributesValuesInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var AttributesValuesIndexFlat
     */
    protected $attributesValuesIndexFlat;

    /**
     * @var array
     */
    protected $enrichers;

    /**
     * @var IndexerConfigInterface
     */
    protected $indexerConfig;

    /**
     * @var AttributeCollectionFactory
     */
    protected $attributesCollectionFactory;

    /**
     * @var LoggerInterface $logger
     * @var IndexerConfigInterface $indexerConfig
     * @var AttributesValuesIndexFlat $attributesValuesIndexFlat
     * @var AttributeCollectionFactory $attributesCollectionFactory
     */
    public function __construct(
        LoggerInterface $logger,
        IndexerConfigInterface $indexerConfig,
        AttributesValuesIndexFlat $attributesValuesIndexFlat,
        AttributeCollectionFactory $attributesCollectionFactory
    )
    {
        $this->logger = $logger;
        $this->indexerConfig = $indexerConfig;
        $this->attributesValuesIndexFlat = $attributesValuesIndexFlat;
        $this->attributesCollectionFactory = $attributesCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute($ids = null)
    {
        $attributesConfigured = $this->indexerConfig->getAttributesWithoutCustoms();

        if ($ids === null) {
            $ids = [];
            $attributes = $this->attributesCollectionFactory->create()
                ->addAttributeToSelect('attribute_id')
                ->addFieldToFilter('attribute_code', $attributesConfigured);

            //TODO: array_map? toArray()? need something more "clean"
            foreach ($attributes as $attribute) {
                $ids[] = $attribute->getId();
            }

        }else{
            $attributes = $this->attributesCollectionFactory->create()
                ->addAttributeToSelect('attribute_id')
                ->addFieldToFilter('attribute_id', $ids)
                ->addFieldToFilter('attribute_code', $attributesConfigured);

            //TODO: array_map? toArray()? need something more "clean"
            foreach ($attributes as $attribute) {
                $ids[] = $attribute->getId();
            }
        }

        if (is_array($ids) && sizeof($ids) === 0) {
            return;
        }

        $stores = $this->indexerConfig->getStores();

        foreach ($stores as $storeId) {

            // Init data

            $data = [];

            // Store data into flat table

            $this->attributesValuesIndexFlat->storeData($data, $storeId);
        }


    }
}
