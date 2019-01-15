<?php

namespace Bitbull\Tooso\Plugin\Model\Layer\Search\CollectionFilter;

use Magento\Catalog\Model\Category;
use Magento\Search\Model\QueryFactory;
use Magento\Framework\Logger\Monolog;

class ApplyToosoSearch extends \Magento\CatalogSearch\Model\Layer\Search\Plugin\CollectionFilter
{

    /**
     * @var Monolog|null
     */
    protected $logger = null;

    /**
     * @param QueryFactory $queryFactory
     * @param Monolog $logger
     */
    public function __construct(QueryFactory $queryFactory, Monolog $logger)
    {
        parent::__construct($queryFactory);
        $this->logger = $logger;
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
        /** @var \Magento\Search\Model\Query $query */
        $query = $this->queryFactory->get();
        /** @var string $queryText */
        $queryText = $query->getQueryText();

        $this->logger->debug("Searching for '$queryText'");
        parent::afterFilter($subject, $result, $collection, $category);
    }
}