<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Service\Indexer\Db;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;
use Bitbull\Tooso\Api\Service\LoggerInterface;
use Magento\Framework\Serialize\Serializer\Json as SerializerJson;

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
     * @var SerializerJson
     */
    protected $serializerJson;

    /**
     * @param ResourceConnection $resource
     * @param LoggerInterface $logger
     * @param SerializerJson $serializerJson
     */
    public function __construct(
        ResourceConnection $resource,
        LoggerInterface $logger,
        SerializerJson $serializerJson
    ) {
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->logger = $logger;
        $this->serializerJson = $serializerJson;
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
        $data = array_map(function($item) use ($storeId, $updateTimeStr) {
            return [
                'store_id' => $storeId,
                'sku' => $item['sku'],
                'data' => $this->serializerJson->serialize($item),
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

        if (sizeof($data) === 0) {
            $this->logger->error('No data into '.self::TABLE_NAME.', reindex is required');
            return null;
        }

        $headers = array_keys($this->serializerJson->unserialize($data[0]['data']));

        return array_merge([$headers], array_map(function ($item){
            return $this->serializerJson->unserialize($item['data']);
        }, $data));
    }
}
