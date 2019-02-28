<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Service\Indexer\Db;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;
use Bitbull\Tooso\Api\Service\LoggerInterface;

class CatalogIndexFlat
{
    /**
     * Table name
     */
    const TABLE_NAME = 'tooso_catalog_flat';

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
     * Store data into database flat table
     *
     * @param array $data
     * @param integer $storeId
     * @return boolean
     */
    public function storeData($data, $storeId)
    {
        $updateTime = new \DateTime();
        $updateTimeStr = $updateTime->format('Y-m-d H:i:s');
        $data = array_map(function($d) use ($storeId, $updateTimeStr) {
            return [
                'store_id' => $storeId,
                'product_id' => $d['id'],
                'data' => serialize($d),
                'update_time' => $updateTimeStr
            ];
        }, $data);

        $this->connection->beginTransaction();
        try {
            $tableName = $this->resource->getTableName(self::TABLE_NAME);
            $this->connection->insertOnDuplicate($tableName, $data);
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            $this->logger->error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Extract data from database flat table
     *
     * @param integer $storeId
     * @return array|null
     * @throws \Exception
     */
    public function extractData($storeId)
    {
        try {
            $tableName = $this->resource->getTableName(self::TABLE_NAME);
            $select = $this->connection->select()->from($tableName)->where('store_id = ?', $storeId);
            $data = $this->connection->query($select)->fetchAll();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return null;
        }

        return array_map(function ($item){
            return unserialize($item['data']);
        }, $data);;
    }
}
