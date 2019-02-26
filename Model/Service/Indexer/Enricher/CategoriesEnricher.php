<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Service\Indexer\Enricher;

use Bitbull\Tooso\Api\Service\Indexer\EnricherInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

class CategoriesEnricher implements EnricherInterface
{
    const CATEGORIES_ATTRIBUTE = 'categories';
    const CATEGORIES_ATTRIBUTE_SEPARATOR = '|';

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var array
     */
    protected $categories;

    /**
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     */
    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        ProductCollectionFactory $productCollectionFactory
    )
    {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute($data)
    {
        $this->loadCategories();

        $ids = array_map(function($d) {
            return $d['id'];
        }, $data);

        $productsCollection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('category_ids ')
            ->addFieldToFilter('entity_id', $ids);

        foreach ($productsCollection as $product) {
            $dataIndex = array_search($product->getId(), $ids, true);
            if ($dataIndex === -1) {
                return; // this shouldn't happen
            }

            $data[$dataIndex][self::CATEGORIES_ATTRIBUTE] = implode(self::CATEGORIES_ATTRIBUTE_SEPARATOR, $this->getProductCategories($product));
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getEnrichedKeys()
    {
        return [self::CATEGORIES_ATTRIBUTE];
    }

    /**
     * Load categories for in-memory association
     */
    protected function loadCategories()
    {
        $categoriesCollection = $this->categoryCollectionFactory->create()
            ->addAttributeToSelect('path')
            ->addAttributeToSelect('name');

        $this->categories = [];
        foreach ($categoriesCollection as $category) {
            $structure = preg_split('#/+#', $category->getPath());
            $pathSize  = count($structure);
            if ($pathSize > 1) {
                $path = array();
                for ($i = 1; $i < $pathSize; $i++) {
                    $path[] = $categoriesCollection->getItemById($structure[$i])->getName();
                }
                $this->categories[$category->getId()] = implode('/', $path);
            }
        }
    }

    /**
     * Return product categories as array of path
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    protected function getProductCategories($product)
    {
        $categories = array();

        $categoriesIds = $product->getCategoryIds();
        foreach ($categoriesIds as $categoryId) {
            if ($this->categories[$categoryId]) {
                $categories[] = $this->categories[$categoryId];
            }
        }

        return $categories;
    }
}
