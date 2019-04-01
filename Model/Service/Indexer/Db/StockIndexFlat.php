<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Service\Indexer\Db;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;
use Bitbull\Tooso\Api\Service\LoggerInterface;

class StockIndexFlat
{
    /**
     * Table name
     */
    const TABLE_NAME = 'cataloginventory_stock_status';

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
     * Extract data from database flat table
     *
     * @return array|null
     */
    public function extractData()
    {
        $headers = ['sku', 'qty', 'is_in_stock'];
        try {
            $tableName = $this->resource->getTableName(self::TABLE_NAME);
            $select = $this->connection
                ->select()
                ->from($tableName, ['catalog_product_entity.sku', 'qty', 'is_in_stock' => 'stock_status'])
                ->distinct('catalog_product_entity.sku')
                ->join(
                    ['catalog_product_entity'], self::TABLE_NAME.'.product_id = catalog_product_entity.entity_id', ['sku']
                );
            $data = $this->connection->query($select)->fetchAll();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return null;
        }

        return array_merge([$headers], $data);
    }
}
