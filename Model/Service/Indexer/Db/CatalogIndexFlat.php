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
     * @throws \Exception
     */
    public function storeData($data, $storeId)
    {
        $updateTime = new \DateTime();
        $updateTimeStr = $updateTime->format('Y-m-d H:i:s');
        $data = array_map(function($item) use ($storeId, $updateTimeStr) {
            $sku = isset($item['sku']) ? $item['sku'] : 'undefined';
            return [
                'store_id' => $storeId,
                'sku' => $sku,
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
     * @param array $headers
     * @return array|null
     */
    public function extractData($storeId, $headers)
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
            return [];
        }

        return array_merge([$headers], array_map(function ($item) use ($headers){
            $unSerializedItem = $this->serializerJson->unserialize($item['data']);
            $resultItem = [];
            foreach ($headers as $header) {
                $resultItem[$header] = isset($unSerializedItem[$header]) ? $unSerializedItem[$header] : null;
            }
            return $resultItem;
        }, $data));
    }

    /**
     * Delete all data from flat table
     *
     * @param integer|null $storeId
     * @return boolean
     */
    public function truncateData($storeId = null)
    {
        $tableName = $this->resource->getTableName(self::TABLE_NAME);

        if ($storeId === null) {
            try {
                $this->connection->truncateTable($tableName);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                return false;
            }
            return true;
        }

        try {
            $this->connection->delete($tableName, ['store_id' => $storeId]);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
        return true;
    }
}
