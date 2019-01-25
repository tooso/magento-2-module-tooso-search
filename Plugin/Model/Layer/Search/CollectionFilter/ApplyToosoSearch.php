<?php declare(strict_types=1);

namespace Bitbull\Tooso\Plugin\Model\Layer\Search\CollectionFilter;

use Bitbull\Tooso\Api\Service\ConfigInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;
use Bitbull\Tooso\Api\Service\Search\RequestParserInterface;
use Bitbull\Tooso\Api\Service\SearchInterface;
use Bitbull\Tooso\Api\Service\SessionInterface;
use Bitbull\Tooso\Block\SearchMessageFactory;
use Magento\Catalog\Model\Category;
use Magento\Search\Model\QueryFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Tooso\SDK\Search\Result;
use Zend_Db_ExprFactory;

class ApplyToosoSearch extends \Magento\CatalogSearch\Model\Layer\Search\Plugin\CollectionFilter
{
    const SEARCH_RESULT_REGISTRY_KEY = 'tooso_search_response';

    /**
     * @var LoggerInterface|null
     */
    protected $logger = null;

    /**
     * @var ConfigInterface|null
     */
    protected $config = null;

    /**
     * @var SearchInterface|null
     */
    protected $search = null;

    /**
     * @var SessionInterface|null
     */
    protected $session = null;

    /**
     * @var MessageManagerInterface|null
     */
    protected $messageManage = null;

    /**
     * @var SearchMessageFactory
     */
    private $searchMessageFactory;

    /**
     * @var Zend_Db_ExprFactory
     */
    private $dbExpressionFactory;

    /**
     * @var RequestParserInterface
     */
    protected $requestParser;

    /**
     * @param QueryFactory $queryFactory
     * @param LoggerInterface $logger
     * @param ConfigInterface $config
     * @param SearchInterface $search
     * @param SessionInterface $session
     * @param MessageManagerInterface $messageManage
     * @param RequestParserInterface $requestParser
     * @param SearchMessageFactory $searchMessageFactory
     * @param Zend_Db_ExprFactory $dbExpressionFactory
     */
    public function __construct(
        QueryFactory $queryFactory,
        LoggerInterface $logger,
        ConfigInterface $config,
        SearchInterface $search,
        SessionInterface $session,
        MessageManagerInterface $messageManage,
        RequestParserInterface $requestParser,
        SearchMessageFactory $searchMessageFactory,
        Zend_Db_ExprFactory $dbExpressionFactory
    )
    {
        parent::__construct($queryFactory);
        $this->logger = $logger;
        $this->config = $config;
        $this->search = $search;
        $this->session = $session;
        $this->messageManage = $messageManage;
        $this->requestParser = $requestParser;
        $this->searchMessageFactory = $searchMessageFactory;
        $this->dbExpressionFactory = $dbExpressionFactory;
    }

    /**
     * @inheritdoc
     */
    public function afterFilter(
        \Magento\Catalog\Model\Layer\Search\CollectionFilter $subject,
        $result,
        $collection,
        Category $category
    ) {
        if ($this->config->isSearchEnabled() !== true) {
            $this->logger->debug('[search] Tooso search is disable, using default Magento search');
            parent::afterFilter($subject, $result, $collection, $category);
            return;
        }

        /** @var \Magento\Search\Model\Query $query */
        $query = $this->queryFactory->get();

        /** @var string $queryText */
        $queryText = $query->getQueryText();

        // Do search
        /** @var Result $searchResult */
        $searchResult = $this->search->execute();

        if ($searchResult->isValid()) {

            // Add similar result alert message
            $similarResultMessage = $searchResult->getSimilarResultsAlert();
            if ($similarResultMessage !== null && $similarResultMessage !== '') {
                $this->messageManage->addMessage($this->searchMessageFactory->create($similarResultMessage));
            }

            if ($searchResult->isSearchAvailable()) {

                // If this query was automatically typo-corrected, save in request scope the searchId for link
                // this query (the parent) with the following one forced as not typo-correct
                if ($this->requestParser->isTypoCorrectedSearch()) {
                    $this->session->setSearchId($searchResult->getSearchId());
                }

                // Automatic typo correction
                if ($searchResult->getFixedSearchString() !== null && $queryText === $searchResult->getOriginalSearchString()) {
                    $message = __(
                        'Search instead for "<a href="%s">%s</a>"',
                        $this->requestParser->buildSearchUrl($searchResult->getOriginalSearchString(), $searchResult->getSearchId()),
                        $searchResult->getOriginalSearchString()
                    );
                    $this->messageManage->addMessage($this->searchMessageFactory->create($message));
                }

                // Check for empty result set
                if ($searchResult->isResultEmpty() && $this->search->isFallbackEnable()) {
                    if ($searchResult->getFixedSearchString() && $queryText === $searchResult->getOriginalSearchString()) {
                        $queryText = $searchResult->getFixedSearchString();
                    }
                    $collection->addSearchFilter($queryText);
                    return;
                }

                // Add search filter
                $products = array_map(function ($product) {
                    return $product['product_id'];
                }, $this->search->getProducts());

                $collection->addAttributeToFilter('entity_id', ['in' => $products]);
                if($this->requestParser->isSortHandled()){
                    $collection->getSelect()->order(
                        $this->dbExpressionFactory->create(
                            ['expression' => 'FIELD(e.entity_id, ' . implode(',', $products) . ')']
                        )
                    );
                }
                return;
            }
        }

        if ($this->search->isFallbackEnable()) {
            $collection->addSearchFilter($queryText);
            return;
        }

        // Apply impossible filter to force not result
        // TODO: refactor this
        $collection->addAttributeToFilter('entity_id', ['null' => true]);
    }
}
