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
     * Get product container selector
     *
     * @return string
     */
    public function getProductContainerSelector()
    {
        return $this->analyticsConfig->getProductContainerSelector();
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
     * Get product link search id attribute name
     *
     * @return string
     */
    public function getSearchIdAttribute()
    {
        return $this->analyticsConfig->getSearchIdAttribute();
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
        $productCollection = $this->search->getSearchCollection();
        if ($productCollection === null || $productCollection->getSize() === 0){
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

        $data = [];
        $index = 0;
        $currentPage = $productCollection->getCurPage();
        $pageSize = $productCollection->getPageSize();
        foreach ($productCollection as $product) {
            $data[$product->getSku()] = $this->tracking->getProductTrackingParams(
                $product,
                $index + ( $pageSize * ($currentPage - 1)),
                1
            );
            $index++;
        }
        return $data;
    }

    /**
     * Get current list page
     *
     * @return integer
     */
    public function getCurrentPage()
    {
        $productCollection = $this->search->getSearchCollection();
        if ($productCollection === null || $productCollection->getSize() === 0){
            return 1;
        }
        return $productCollection->getCurPage();
    }

    /**
     * Get current page size
     *
     * @return integer
     */
    public function getCurrentPageSize()
    {
        $productCollection = $this->search->getSearchCollection();
        if ($productCollection === null || $productCollection->getSize() === 0){
            return 0;
        }
        return $productCollection->getPageSize();
    }

    /**
     * @inheritdoc
     */
    public function _toHtml()
    {
        if ($this->analyticsConfig->isLibraryIncluded() === false) {
            return '';
        }
        return parent::_toHtml();
    }
}
