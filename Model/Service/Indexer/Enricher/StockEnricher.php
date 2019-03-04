<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Service\Indexer\Enricher;

use Bitbull\Tooso\Api\Service\Indexer\EnricherInterface;
use Bitbull\Tooso\Model\Service\Indexer\Db\StockIndexFlat;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;
use Bitbull\Tooso\Api\Service\LoggerInterface;

class StockEnricher implements EnricherInterface
{
    /**
     * @var array
     */
    protected $stockStatus = [];

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ResourceConnection $resource
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resource,
        LoggerInterface $logger
    ) {
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute($data)
    {
        $this->loadStockStatus();

        array_walk($data, function(&$elem) {
            if (isset($this->stockStatus[$elem['id']]) === false){
                $elem['is_in_stock'] = null;
                $elem['qty'] = null;
                return; // this shouldn't happen
            }
            $elem['is_in_stock'] = $this->stockStatus[$elem['id']]['stock_status'];
            $elem['qty'] = $this->stockStatus[$elem['id']]['qty'];
        });

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getEnrichedKeys()
    {
        return ['is_in_stock', 'qty'];
    }

    /**
     * Load stock status for in-memory association
     */
    protected function loadStockStatus()
    {
        $this->stockStatus = [];

        /**
         * Collection \Magento\CatalogInventory\Api\Data\StockStatusCollectionInterface cannot be created
         * due an error 'Configuration array must have a key for 'dbname' that names the database instance' on factory create.
         * Using plain database access instead:
         */

        try {
            $tableName = $this->resource->getTableName(StockIndexFlat::TABLE_NAME);
            $select = $this->connection
                ->select()
                ->from($tableName, ['product_id', 'qty', 'stock_status'])
                ->distinct('product_id');
            $data = $this->connection->query($select)->fetchAll();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return null;
        }

        foreach ($data as $stockStatus) {
            $this->stockStatus[$stockStatus['product_id']] = $stockStatus;
        }
    }

}
