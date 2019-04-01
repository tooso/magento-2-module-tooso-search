<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Service\Indexer\Enricher;

use Bitbull\Tooso\Api\Service\Indexer\EnricherInterface;
use Bitbull\Tooso\Api\Service\Config\IndexerConfigInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

class AttributesEnricher implements EnricherInterface
{
    /**
     * @var IndexerConfigInterface
     */
    protected $indexerConfig;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * AttributesEnricher constructor.
     *
     * @param IndexerConfigInterface $indexerConfig
     * @param ProductCollectionFactory $productCollectionFactory
     */
    public function __construct(
        IndexerConfigInterface $indexerConfig,
        ProductCollectionFactory $productCollectionFactory
    ) {
        $this->indexerConfig = $indexerConfig;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute($data)
    {
        $ids = array_map(function($elem) {
            return $elem['id'];
        }, $data);

        $attributes = $this->indexerConfig->getAttributesWithoutCustoms();

        $productsCollection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('sku')
            ->addFieldToFilter('entity_id', $ids);

        foreach ($attributes as $attribute) {
            $productsCollection->addAttributeToSelect($attribute);
        }

        foreach ($productsCollection as $product) {
            $dataIndex = array_search($product->getId(), $ids, true);
            if ($dataIndex === -1) {
                continue; // this shouldn't happen
            }
            array_walk($attributes, function ($attribute) use($dataIndex, $product, &$data) {
                $data[$dataIndex][$attribute] = $product->getData($attribute);
            });
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getEnrichedKeys()
    {
        return $this->indexerConfig->getAttributesWithoutCustoms();
    }
}
