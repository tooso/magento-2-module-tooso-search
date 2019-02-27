<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Service\Indexer\Enricher;

use Bitbull\Tooso\Api\Service\Config\IndexerConfigInterface;
use Bitbull\Tooso\Api\Service\Indexer\EnricherInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

class VariantsEnricher implements EnricherInterface
{
    const VARIANTS_ATTRIBUTE = 'variants';

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;
    /**
     * @var IndexerConfigInterface
     */
    protected $indexerConfig;

    /**
     * @param ProductCollectionFactory $productCollectionFactory
     * @param IndexerConfigInterface $indexerConfig
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        IndexerConfigInterface $indexerConfig
    )
    {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->indexerConfig = $indexerConfig;
    }

    /**
     * @inheritdoc
     */
    public function execute($data)
    {
        $ids = array_map(function($elem) {
            return $elem['id'];
        }, $data);

        $productsCollection = $this->productCollectionFactory->create()
            ->addFieldToFilter('entity_id', $ids);

        foreach ($productsCollection as $product) {
            $dataIndex = array_search($product->getId(), $ids, true);
            if ($dataIndex === -1) {
                return; // this shouldn't happen
            }

            $variants = $this->getProductVariants($product);
            if(sizeof($variants) > 0){
                $data[$dataIndex][self::VARIANTS_ATTRIBUTE] = json_encode($variants);
            }
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getEnrichedKeys()
    {
        return [self::VARIANTS_ATTRIBUTE];
    }

    /**
     * Get product variants
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    protected function getProductVariants($product)
    {
        if ($product->getTypeId() !== 'configurable') {
            return [];
        }

        $variantsIds = $product->getTypeInstance()->getUsedProductIds($product);
        $variantsCollection = $this->productCollectionFactory->create()
            ->addFieldToFilter('entity_id', $variantsIds);

        $attributes = $this->indexerConfig->getSimpleAttributes();
        foreach ($attributes as $attribute) {
            $variantsCollection->addAttributeToSelect($attribute);
        }

        $variants = [];
        foreach ($variantsCollection as $variant) {
            $variantData = [];
            foreach ($attributes as $attribute) {
                $variantData[$attribute] = $variant->getData($attribute);
            }
            $variants[] = $variantData;
        }

        return $variants;
    }
}
