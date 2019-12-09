<?php declare(strict_types=1);

namespace Bitbull\Tooso\Plugin\Model\Layer\Search\CollectionFilter;

use Bitbull\Tooso\Api\Service\ConfigInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;
use Bitbull\Tooso\Api\Service\Search\RequestParserInterface;
use Bitbull\Tooso\Api\Service\SearchInterface;
use Bitbull\Tooso\Api\Service\SessionInterface;
use Bitbull\Tooso\Block\Search\SearchMessageFactory;
use Magento\Catalog\Model\Category;
use Magento\Search\Model\QueryFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Tooso\SDK\Search\Result;
use Zend_Db_ExprFactory;

class ApplyToosoSearch extends \Magento\CatalogSearch\Model\Layer\Search\Plugin\CollectionFilter
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var SearchInterface
     */
    protected $search;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var MessageManagerInterface
     */
    protected $messageManage;

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
            $this->logger->debug('[search plugin] Tooso search is disable, using default Magento search');
            parent::afterFilter($subject, $result, $collection, $category);
            return;
        }

        $this->search->registerSearchCollection($collection);

        /** @var \Magento\Search\Model\Query $query */
        $query = $this->queryFactory->get();

        /** @var string $queryText */
        $queryText = $query->getQueryText();

        $this->logger->debug('[search plugin] Executing search..');

        // Do search
        /** @var Result $searchResult */
        $searchResult = $this->search->execute();

        if ($searchResult->isValid()) {

            // Add similar result alert message
            $similarResultMessage = $searchResult->getSimilarResultsAlert();
            if ($similarResultMessage !== null && $similarResultMessage !== '') {
                $this->logger->debug('[search plugin] Adding custom frontend message');
                $this->messageManage->addMessage($this->searchMessageFactory->create([
                    'text' => $similarResultMessage
                ]));
            }

            if ($searchResult->isSearchAvailable()) {

                // If this query was automatically typo-corrected, save in request scope the searchId for link
                // this query (the parent) with the following one forced as not typo-correct
                if ($this->requestParser->isTypoCorrectedSearch()) {
                    $this->logger->debug('[search plugin] Is typo corrected search, setting search id');
                    $this->session->setSearchId($searchResult->getSearchId());
                }

                // Automatic typo correction
                if ($searchResult->getFixedSearchString() !== null && $queryText === $searchResult->getOriginalSearchString()) {
                    $this->logger->debug('[search plugin] Query text is typo corrected, adding frontend message');
                    $message = __(
                        'Search instead for "<a href="%1">%2</a>"',
                        $this->requestParser->buildSearchUrl($searchResult->getOriginalSearchString(), $searchResult->getSearchId()),
                        $searchResult->getOriginalSearchString()
                    );
                    $this->messageManage->addMessage($this->searchMessageFactory->create([
                        'text' => $message
                    ]));
                }

                // Check for empty result set
                if ($searchResult->isResultEmpty()){
                    if ($this->search->isFallbackEnable()) {
                        if ($searchResult->getFixedSearchString() && $queryText === $searchResult->getOriginalSearchString()) {
                            $queryText = $searchResult->getFixedSearchString();
                        }
                        $collection->addSearchFilter($queryText);
                        $this->logger->debug('[search plugin] Executing Magento search fallback');
                        return;
                    }

                    $this->setEmptyFilter($collection);
                    return;
                }

                // Add search filter
                $products = array_map(function ($product) {
                    return $product['product_id'];
                }, $this->search->getProducts());

                if (sizeof($products) === 0) {
                    $this->logger->error('[search plugin] No product found with SKU response, check products with SKUs: '.implode(',', $searchResult->getResults()));
                    if ($this->search->isFallbackEnable()) {
                        $collection->addSearchFilter($queryText);
                        $this->logger->debug('[search plugin] Executing Magento search fallback');
                        return;
                    }
                    $this->setEmptyFilter($collection);
                    return;
                }

                $this->logger->debug('[search plugin] Filter entity_id with ids: '.implode(',', $products));

                /**
                 * Be compatible with both \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection and \Magento\Catalog\Model\ResourceModel\Product\Collection
                 */
                if ($collection instanceof \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection ||
                    $collection instanceof \Magento\Catalog\Model\ResourceModel\Product\Collection) {
                    $collection->addAttributeToFilter('entity_id', [
                        'in' => $products
                    ]);
                } else {
                    $collection->addFieldToFilter('entity_id', $products);
                }

                if($this->requestParser->isSortHandled()){
                    $this->logger->debug('[search plugin] Forcing sort from search response');
                    $collection->getSelect()->order(
                        $this->dbExpressionFactory->create(
                            ['expression' => 'FIELD(e.entity_id, ' . implode(',', $products) . ')']
                        )
                    );
                }
                return;
            }
        }else{
            $this->logger->debug('[search plugin] Search response not valid');
        }

        if ($this->search->isFallbackEnable()) {
            $collection->addSearchFilter($queryText);
            $this->logger->debug('[search plugin] Executing Magento search fallback');
            return;
        }

        $this->setEmptyFilter($collection);
    }

    /**
     * Force an empty result set
     *
     * @param $collection
     */
    protected function setEmptyFilter(&$collection)
    {
        // TODO: refactor this

        $this->logger->debug('[search plugin] No results, forcing result to 0');
        $collection->addAttributeToFilter('entity_id', ['null' => true]);
    }
}
