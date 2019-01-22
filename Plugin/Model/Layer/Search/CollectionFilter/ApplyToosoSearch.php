<?php declare(strict_types=1);

namespace Bitbull\Tooso\Plugin\Model\Layer\Search\CollectionFilter;

use Bitbull\Tooso\Api\Service\ConfigInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;
use Bitbull\Tooso\Api\Service\SearchInterface;
use Bitbull\Tooso\Api\Service\SessionInterface;
use Bitbull\Tooso\Block\SearchMessage;
use Magento\Catalog\Model\Category;
use Magento\Search\Model\QueryFactory;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Tooso\SDK\Search\Result;

class ApplyToosoSearch extends \Magento\CatalogSearch\Model\Layer\Search\Plugin\CollectionFilter
{

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
     * @param QueryFactory $queryFactory
     * @param LoggerInterface $logger
     * @param ConfigInterface $config
     * @param SearchInterface $search
     * @param SessionInterface $session
     */
    public function __construct(QueryFactory $queryFactory, LoggerInterface $logger, ConfigInterface $config, SearchInterface $search, SessionInterface $session, MessageManagerInterface $messageManage)
    {
        parent::__construct($queryFactory);
        $this->logger = $logger;
        $this->config = $config;
        $this->search = $search;
        $this->session = $session;
        $this->messageManage = $messageManage;
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

        $typoCorrection = $this->search->isTypoCorrectedSearch();
        $parentSearchId = null;
        if ($typoCorrection === false) {
            $parentSearchId = $this->search->getParentSearchId();
        }

        // Do search
        /** @var Result $result */
        $result = $this->search->execute($queryText, $typoCorrection, $parentSearchId);

        // Check for redirect
        $redirect = $result->getRedirect();
        if ($redirect !== null) {
            // TODO: respond with redirect to URL contained in $redirect variable
            return;
        }

        // Add similar result alert message
        $similarResultMessage = $result->getSimilarResultsAlert();
        if ($similarResultMessage !== null && $similarResultMessage !== '') {
            $this->messageManage->addMessage(new SearchMessage($similarResultMessage));
        }

        if ($result->isSearchAvailable()) {

            // If this query was automatically typo-corrected, save in request scope the searchId for link
            // this query (the parent) with the following one forced as not typo-correct
            if ($this->search->isTypoCorrectedSearch()) {
                $this->session->setSearchId($result->getSearchId());
            }

            // Automatic typo correction
            if ($result->getFixedSearchString() && $queryText === $result->getOriginalSearchString()) {
                $message = sprintf(
                    __('Search instead for "<a href="%s">%s</a>"'),
                    $this->search->getSearchUrl($result->getOriginalSearchString(), $result->getSearchId()),
                    $result->getOriginalSearchString()
                );
                $this->messageManage->addMessage(new SearchMessage($message));
            }

            // Check for empty result set
            if ($result->isResultEmpty() && $this->search->isFallbackEnable()) {
                if ($result->getFixedSearchString() && $queryText === $result->getOriginalSearchString()) {
                    $queryText = $result->getFixedSearchString();
                }
                $collection->addSearchFilter($queryText);
                return;
            }

            // Add search filter
            $products = array();
            foreach ($this->search->getProducts() as $product) {
                $products[] = $product['product_id'];
            }
            $collection->addAttributeToFilter('entity_id', array('in' => $products));
            $collection->getSelect()->order(new \Zend_Db_Expr('FIELD(e.entity_id, ' . implode(',', $products) . ')'));
            return;
        }

        if ($this->search->isFallbackEnable()) {
            $collection->addSearchFilter($queryText);
            return;
        }

        // Apply impossible filter to force not result
        // TODO: refactor this
        $collection->addAttributeToFilter('entity_id', array('null' => true));
    }
}
