<?php
namespace Bitbull\Tooso\Block\Tracking;

use Bitbull\Tooso\Api\Block\ScriptInterface;
use Bitbull\Tooso\Api\Service\ConfigInterface;
use Bitbull\Tooso\Api\Service\SearchInterface;
use Magento\Framework\View\Element\Template\Context;
use Bitbull\Tooso\Api\Service\Config\AnalyticsConfigInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Bitbull\Tooso\Api\Service\TrackingInterface;

class ClickAfterSearch extends \Magento\Framework\View\Element\Template implements ScriptInterface
{
    const SCRIPT_ID = 'tooso-tracking-click-after-search';

    /**
     * @var SearchInterface
     */
    public $search;

    /**
     * @var ConfigInterface
     */
    public $config;

    /**
     * @var AnalyticsConfigInterface
     */
    public $analyticsConfig;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var TrackingInterface
     */
    protected $tracking;

    /**
     * ClickAfterSearch constructor.
     *
     * @param Context $context
     * @param SearchInterface $search
     * @param ConfigInterface $config
     * @param AnalyticsConfigInterface $analyticsConfig
     * @param ProductCollectionFactory $productCollectionFactory
     * @param TrackingInterface $tracking
     */
    public function __construct(
        Context $context,
        SearchInterface $search,
        ConfigInterface $config,
        AnalyticsConfigInterface $analyticsConfig,
        ProductCollectionFactory $productCollectionFactory,
        TrackingInterface $tracking
    ) {
        parent::__construct($context);
        $this->search = $search;
        $this->config = $config;
        $this->analyticsConfig = $analyticsConfig;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->tracking = $tracking;
    }

    /**
     * Get search result
     *
     * @return \Tooso\SDK\Search\Result
     */
    public function getSearchResult()
    {
        return $this->search->getResult();
    }

    /**
     * Get product link selector
     *
     * @return string
     */
    public function getProductLinkSelector()
    {
        return $this->analyticsConfig->getProductLinkSelector();
    }

    /**
     * Get product link attribute name
     *
     * @return string
     */
    public function getProductAttributeName()
    {
        return $this->analyticsConfig->getProductAttributeName();
    }

    /**
     * @inheritdoc
     */
    public function getScriptId()
    {
        return self::SCRIPT_ID;
    }

    /**
     * Get product listed in page
     *
     * @return array
     */
    public function getProducts()
    {
        $skus = $this->getSearchResult()->getResults();
        $productCollection = $this->productCollectionFactory->create()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('manufacturer')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('sku', $skus);
        $data = [];

        if ($productCollection->getSize() === 0){
            return [];
        }

        $categoriesIds = [];
        foreach ($productCollection as $product) {
            $productCategories = $product->getCategoryIds();
            if (!is_array($productCategories) || sizeof($productCategories) === 0){
                continue;
            }
            $categoriesIds[] = $productCategories[0];
        }

        if (sizeof($categoriesIds) !== 0){
            $this->tracking->loadCategories($categoriesIds);
        }

        foreach ($productCollection as $index => $product) {
            $data[$product->getSku()] = $this->tracking->getProductTrackingParams(
                $product,
                $index,
                1
            );
        }
        return $data;
    }
}
