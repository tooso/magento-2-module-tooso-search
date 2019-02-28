<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Service\Indexer\Enricher;

use Bitbull\Tooso\Api\Service\Config\IndexerConfigInterface;
use Bitbull\Tooso\Api\Service\Indexer\EnricherInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;

class GalleryEnricher implements EnricherInterface
{
    const GALLERY_ATTRIBUTE = 'gallery';
    const GALLERY_ATTRIBUTE_SEPARATOR = '|';

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var IndexerConfigInterface
     */
    protected $indexerConfig;

    /**
     * @var GalleryReadHandler
     */
    protected $galleryReadHandler;

    /**
     * @param ProductCollectionFactory $productCollectionFactory
     * @param GalleryReadHandler $galleryReadHandler
     * @param IndexerConfigInterface $indexerConfig
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        GalleryReadHandler $galleryReadHandler,
        IndexerConfigInterface $indexerConfig
    )
    {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->galleryReadHandler = $galleryReadHandler;
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
            ->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', $ids);

        foreach ($productsCollection as $product) {
            $dataIndex = array_search($product->getId(), $ids, true);
            if ($dataIndex === -1) {
                return; // this shouldn't happen
            }

            $this->galleryReadHandler->execute($product);
            /** @var \Magento\Framework\Data\Collection $mediaGallery */
            $mediaGallery = $product->getMediaGalleryImages();
            if($mediaGallery === null){
                $data[$dataIndex][self::GALLERY_ATTRIBUTE] = null;
                continue;
            }

            $imagesItems = $mediaGallery->getItems();
            if (is_array($imagesItems) === false || sizeof($imagesItems) === 0) {
                $data[$dataIndex][self::GALLERY_ATTRIBUTE] = null;
                continue;
            }

            $images = array_map(function ($imageItem) {
                return $imageItem->getUrl();
            }, $imagesItems);
            $data[$dataIndex][self::GALLERY_ATTRIBUTE] = implode(self::GALLERY_ATTRIBUTE_SEPARATOR, $images);
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getEnrichedKeys()
    {
        return [self::GALLERY_ATTRIBUTE];
    }
}
