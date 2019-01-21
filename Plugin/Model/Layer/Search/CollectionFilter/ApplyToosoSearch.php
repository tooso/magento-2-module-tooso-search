<?php declare(strict_types=1);

namespace Bitbull\Tooso\Plugin\Model\Layer\Search\CollectionFilter;

use Bitbull\Tooso\Api\Service\ConfigInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;
use Bitbull\Tooso\Api\Service\SearchInterface;
use Magento\Catalog\Model\Category;
use Magento\Search\Model\QueryFactory;
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
     * @param QueryFactory $queryFactory
     * @param LoggerInterface $logger
     * @param ConfigInterface $config
     * @param SearchInterface $search
     */
    public function __construct(QueryFactory $queryFactory, LoggerInterface $logger, ConfigInterface $config, SearchInterface $search)
    {
        parent::__construct($queryFactory);
        $this->logger = $logger;
        $this->config = $config;
        $this->search = $search;
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
        if ($this->config->isSearchEnabled() !== true){
            $this->logger->debug('[search] Tooso search is disable, using default Magento search');
            parent::afterFilter($subject, $result, $collection, $category);
            return;
        }

        /** @var \Magento\Search\Model\Query $query */
        $query = $this->queryFactory->get();

        /** @var string $queryText */
        $queryText = $query->getQueryText();

        $this->logger->debug("[search] Searching for '$queryText'");
        $result = $this->search->execute($queryText);
        $products = $result->getResults();
        $collection->addAttributeToFilter('sku', array('in' => (sizeof($products) > 0) ? $products : array(0)));
        $this->logger->debug('[search] Searching query updated');
    }
}
