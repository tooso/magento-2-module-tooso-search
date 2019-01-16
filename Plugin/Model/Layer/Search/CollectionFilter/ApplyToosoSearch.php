<?php

namespace Bitbull\Tooso\Plugin\Model\Layer\Search\CollectionFilter;

use Bitbull\Tooso\Api\Service\ConfigInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;
use Magento\Catalog\Model\Category;
use Magento\Search\Model\QueryFactory;

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
     * @param QueryFactory $queryFactory
     * @param LoggerInterface $logger
     */
    public function __construct(QueryFactory $queryFactory, LoggerInterface $logger, ConfigInterface $config)
    {
        parent::__construct($queryFactory);
        $this->logger = $logger;
        $this->config = $config;
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
        if ($this->config->isSearchEnabled() !== true){
            return parent::afterFilter($subject, $result, $collection, $category);
        }


    }
}