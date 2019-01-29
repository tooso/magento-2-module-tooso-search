<?php
namespace Bitbull\Tooso\Plugin\Block;

use Bitbull\Tooso\Api\Service\SearchInterface;
use Magento\CatalogSearch\Helper\Data;
use Magento\Search\Model\QueryFactory;

class Result
{
    const SEARCH_RESULT_MSG = "Search results for: '%1'";

    /**
     * @var QueryFactory
     */
    protected $queryFactory;

    /**
     * @var Data
     */
    protected $catalogSearchData;

    /**
     * @var SearchInterface
     */
    protected $search;

    /**
     * @param SearchInterface $search
     * @param Data $catalogSearchData
     */
    public function __construct(Data $catalogSearchData, SearchInterface $search)
    {
        $this->catalogSearchData = $catalogSearchData;
        $this->search = $search;
    }

    /**
     * @param \Magento\CatalogSearch\Block\Result $subject
     * @return \Magento\Framework\Phrase
     */
    public function afterGetSearchQueryText(\Magento\CatalogSearch\Block\Result $subject)
    {
        $searchResult = $this->search->getResult();
        $searchQuery = $this->catalogSearchData->getEscapedQueryText();
        if ($searchResult !== null && $searchResult->isValid() && $searchResult->getFixedSearchString() !== null) {
            $searchQuery = $searchResult->getFixedSearchString();
        }
        return __(self::SEARCH_RESULT_MSG, $searchQuery);
    }
}
