<?php
namespace Bitbull\Tooso\Block\Search;

use Bitbull\Tooso\Api\Service\SearchInterface;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\CatalogSearch\Helper\Data;
use Magento\Framework\View\Element\Template\Context;
use Magento\Search\Model\QueryFactory;

class Result extends \Magento\CatalogSearch\Block\Result
{
    const SEARCH_RESULT_MSG = "Search results for: '%1'";

    /**
     * @var SearchInterface
     */
    protected $search;

    /**
     * @param Context $context
     * @param LayerResolver $layerResolver
     * @param Data $catalogSearchData
     * @param QueryFactory $queryFactory
     * @param SearchInterface $search
     * @param array $data
     */
    public function __construct(Context $context, LayerResolver $layerResolver, Data $catalogSearchData, QueryFactory $queryFactory, SearchInterface $search, array $data = [])
    {
        parent::__construct($context, $layerResolver, $catalogSearchData, $queryFactory, $data);
        $this->search = $search;
    }

    /**
     * @inheritdoc
     */
    public function getSearchQueryText()
    {
        $searchResult = $this->search->getResult();
        $searchQuery = $this->catalogSearchData->getEscapedQueryText();
        if ($searchResult !== null && $searchResult->isValid() && $searchResult->getFixedSearchString() !== null) {
            $searchQuery = $searchResult->getFixedSearchString();
        }
        return __(self::SEARCH_RESULT_MSG, $searchQuery);
    }
}
