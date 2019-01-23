<?php declare(strict_types=1);

namespace Bitbull\Tooso\Model\Service;

use Bitbull\Tooso\Api\Service\Config\SearchConfigInterface;
use Bitbull\Tooso\Api\Service\ConfigInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;
use Bitbull\Tooso\Api\Service\SearchInterface;
use Bitbull\Tooso\Api\Service\TrackingInterface;
use Magento\Framework\UrlFactory;
use Tooso\SDK\Exception;
use Tooso\SDK\ClientBuilder;
use Tooso\SDK\Search\Result;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\Registry;

class Search implements SearchInterface
{
    const SEARCH_RESULT_REGISTRY_KEY = 'tooso_search_response';
    const PARAM_PARENT_SEARCH_ID = 'parentSearchId';
    const PARAM_TYPO_CORRECTION = 'typoCorrection';

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var SearchConfigInterface
     */
    protected $searchConfig;

    /**
     * @var TrackingInterface
     */
    protected $tracking;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ClientBuilder
     */
    protected $clientBuilder;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var Registry|null
     */
    protected $registry = null;

    /**
     * @var UrlFactory
     */
    protected $urlFactory;

    /**
     * @var Result
     */
    protected $result;

    /**
     * @var RequestHttp
     */
    protected $request;

    /**
     * Search constructor.
     *
     * @param ConfigInterface $config
     * @param SearchConfigInterface $searchConfig
     * @param TrackingInterface $tracking
     * @param LoggerInterface $logger
     * @param ResourceConnection $resourceConnection
     * @param Registry $registry
     * @param UrlFactory $urlFactory
     * @param RequestHttp $request
     */
    public function __construct(
        ConfigInterface $config,
        SearchConfigInterface $searchConfig,
        TrackingInterface $tracking,
        LoggerInterface $logger,
        ResourceConnection $resourceConnection,
        Registry $registry,
        UrlFactory $urlFactory,
        RequestHttp $request
    )
    {
        $this->config = $config;
        $this->searchConfig = $searchConfig;
        $this->tracking = $tracking;
        $this->logger = $logger;
        $this->resourceConnection = $resourceConnection;
        $this->registry = $registry;
        $this->urlFactory = $urlFactory;
        $this->request = $request;
        $this->clientBuilder = new ClientBuilder();
    }

    /**
     * @inheritdoc
     */
    public function execute($query, $page = null, $limit = null)
    {
        $result = $this->getResult();

        if ($result !== null) {
            return $result;
        }

        $typoCorrection = $this->isTypoCorrectedSearch();
        $parentSearchId = null;
        if ($typoCorrection === false) {
            $parentSearchId = $this->getParentSearchId();
        }

        // Do search
        try {
            $params = $this->tracking->getProfilingParams();

            if ($parentSearchId !== null) {
                $params[self::PARAM_PARENT_SEARCH_ID] = $parentSearchId;
            }

            if ($limit === null) {
                $limit = $this->searchConfig->getDefaultLimit();
            }

            $result = $this->getClient()->search(
                $query,
                $typoCorrection,
                $params,
                $this->searchConfig->isEnriched(),
                $page,
                $limit
            );

        } catch (Exception $e) {
            $this->logger->logException($e);
            $result = new Result();
        }

        $this->setResult($result);
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getResult()
    {
        // Check if class instance has result
        if ($this->result !== null) {
            return $this->result;
        }

        // Check if registry has result and set it into class instance property
        $this->result = $this->registry->registry(self::SEARCH_RESULT_REGISTRY_KEY);
        if ($this->result !== null) {
            return $this->result;
        }

        return null;
    }

    /**
     * Set result
     *
     * @param Result $result
     */
    protected function setResult($result)
    {
        $this->result = $result;

        // Store result into registry
        $this->registry->register(self::SEARCH_RESULT_REGISTRY_KEY, $this->result, true);
    }

    /**
     * @inheritdoc
     */
    public function getProducts()
    {
        $products = array();

        if ($this->result !== null) {

            $skus = [];
            if($this->searchConfig->isEnriched()){
                $resultProducts = $this->result->getResults();
                foreach ($resultProducts as $product) {
                    if(!is_object($product)){
                        $skus = $this->result->getResults();
                        break;
                    }
                    $skus[] = $product->sku;
                }
            }else{
                $skus = $this->result->getResults();
            }

            $i = 1;
            $productIds = $this->getIdsBySkus($skus);

            foreach ($skus as $sku) {
                if (isset($productIds[$sku])) {
                    $products[] = array(
                        'sku' => $sku,
                        'product_id' => $productIds[$sku],
                        'relevance' => $i
                    );
                }

                $i++;
            }
        }

        return $products;
    }

    /**
     * @inheritdoc
     */
    public function getParentSearchId()
    {
        return $this->request->getParam(self::PARAM_PARENT_SEARCH_ID);
    }

    /**
     * @inheritdoc
     */
    public function getSearchUrl($queryParam, $parentSearchId)
    {
        $url = $this->urlFactory->create();
        $url->setQueryParam('q', $queryParam);
        $url->setQueryParam(self::PARAM_PARENT_SEARCH_ID, $parentSearchId);
        return $url->getUrl('catalogsearch/result');
    }

    /**
     * @inheritdoc
     */
    public function isTypoCorrectedSearch()
    {
        return $this->request->getParam(self::PARAM_PARENT_SEARCH_ID, 'true') === 'true';
    }

    /**
     * @inheritdoc
     */
    public function isFallbackEnable()
    {
        return $this->searchConfig->isFallbackEnable();
    }

    /**
     * Get products identifiers by skus
     *
     * @param array $skus
     * @return array
     */
    protected function getIdsBySkus($skus)
    {
        if (count($skus) === 0) return array();

        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('catalog_product_entity');

        $where = 'sku IN (';
        $bind = array();

        // Build the where clause with all the required placeholder for binding
        for ($i=0, $iMax = count($skus); $i < $iMax; $i++) {
            $bind[':sku' . $i] = $skus[$i];
        }

        $where .= implode(',', array_keys($bind)) . ')';

        $select = $connection->select()
            ->from($tableName, array('sku', 'entity_id'))
            ->where($where);

        return $connection->fetchPairs($select, $bind);
    }

    /**
     * Get Client
     *
     * @return \Tooso\SDK\Client
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getClient()
    {
        return $this->clientBuilder
            ->withApiKey($this->config->getApiKey())
            ->withApiVersion($this->config->getApiVersion())
            ->withApiBaseUrl($this->config->getApiBaseUrl())
            ->withLanguage($this->config->getLanguage())
            ->withStoreCode($this->config->getStoreCode())
            ->withAgent($this->tracking->getApiAgent())
            ->withLogger($this->logger)
            ->build();
    }
}
