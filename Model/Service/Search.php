<?php declare(strict_types=1);

namespace Bitbull\Tooso\Model\Service;

use Bitbull\Tooso\Api\Service\Config\SearchConfigInterface;
use Bitbull\Tooso\Api\Service\ConfigInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;
use Bitbull\Tooso\Api\Service\Search\RequestParserInterface;
use Bitbull\Tooso\Api\Service\SearchInterface;
use Bitbull\Tooso\Api\Service\TrackingInterface;
use Tooso\SDK\Exception;
use Tooso\SDK\ClientBuilder;
use Tooso\SDK\Search\ResultFactory;
use Tooso\SDK\Search\Result;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Registry;

class Search implements SearchInterface
{
    const SEARCH_RESULT_REGISTRY_KEY = 'tooso_search_response';

    const PARAM_FILTER = 'filter';
    const PARAM_ORDER = 'sort';
    const PARAM_PARENT_SEARCH_ID = 'parentSearchId';

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
     * @var Registry
     */
    protected $registry;

    /**
     * @var RequestParserInterface
     */
    protected $requestParser;

    /**
     * @var Result
     */
    protected $result;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * Search constructor.
     *
     * @param ConfigInterface $config
     * @param SearchConfigInterface $searchConfig
     * @param TrackingInterface $tracking
     * @param LoggerInterface $logger
     * @param ResourceConnection $resourceConnection
     * @param Registry $registry
     * @param RequestParserInterface $requestParser
     * @param ClientBuilder $clientBuilder,
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        ConfigInterface $config,
        SearchConfigInterface $searchConfig,
        TrackingInterface $tracking,
        LoggerInterface $logger,
        ResourceConnection $resourceConnection,
        Registry $registry,
        RequestParserInterface $requestParser,
        ClientBuilder $clientBuilder,
        ResultFactory $resultFactory
    )
    {
        $this->config = $config;
        $this->searchConfig = $searchConfig;
        $this->tracking = $tracking;
        $this->logger = $logger;
        $this->resourceConnection = $resourceConnection;
        $this->registry = $registry;
        $this->requestParser = $requestParser;
        $this->clientBuilder = $clientBuilder;
        $this->resultFactory = $resultFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $result = $this->getResult();

        if ($result !== null) {
            return $result;
        }

        $queryText = $this->requestParser->getQueryText();

        $typoCorrection = $this->requestParser->isTypoCorrectedSearch();
        $parentSearchId = null;
        if ($typoCorrection === false) {
            $parentSearchId = $this->requestParser->getParentSearchId();
        }

        // Do search
        try {
            $params = $this->tracking->getProfilingParams();

            if ($parentSearchId !== null) {
                $params[self::PARAM_PARENT_SEARCH_ID] = $parentSearchId;
            }

            if (true) {
                $params[self::PARAM_FILTER] = $this->requestParser->getFilterParam();
            }
            if (true) {
                $params[self::PARAM_ORDER] = $this->requestParser->getOrderParam();
            }

            $limit = $this->searchConfig->getDefaultLimit();

            //NOTE: at this point page and limit param are not used
            $result = $this->getClient()->search(
                $queryText,
                $typoCorrection,
                $params,
                $this->searchConfig->isEnriched(),
                null,
                $limit
            );
        } catch (Exception $e) {
            $this->logger->logException($e);
            $result = $this->resultFactory->create();
        }

        $this->registerResult($result);
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getResult()
    {
        // Check if class instance has result
        if (!$this->result) {
            $this->result = $this->registry->registry(self::SEARCH_RESULT_REGISTRY_KEY);
        }

        return $this->result;
    }

    /**
     * Set result
     *
     * @param Result $result
     */
    protected function registerResult($result)
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
        $products = [];

        if ($this->result !== null) {
            $skus = [];
            if ($this->searchConfig->isEnriched()) {
                $resultProducts = $this->result->getResults();
                foreach ($resultProducts as $product) {
                    if (!is_object($product)) {
                        $skus = $this->result->getResults();
                        break;
                    }
                    $skus[] = $product->sku;
                }
            } else {
                $skus = $this->result->getResults();
            }

            $i = 1;
            $productIds = $this->getIdsBySkus($skus);

            foreach ($skus as $sku) {
                if (isset($productIds[$sku])) {
                    $products[] = [
                        'sku' => $sku,
                        'product_id' => $productIds[$sku],
                        'relevance' => $i
                    ];
                }

                $i++;
            }
        }

        return $products;
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
        if (count($skus) === 0) {
            return [];
        }

        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('catalog_product_entity');

        $where = 'sku IN (';
        $bind = [];

        // Build the where clause with all the required placeholder for binding
        for ($i=0, $iMax = count($skus); $i < $iMax; $i++) {
            $bind[':sku' . $i] = $skus[$i];
        }

        $where .= implode(',', array_keys($bind)) . ')';

        $select = $connection->select()
            ->from($tableName, ['sku', 'entity_id'])
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
