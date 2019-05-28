<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Service\Indexer\Db;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\App\ResourceConnection;
use Bitbull\Tooso\Api\Service\LoggerInterface;

class AttributesValuesIndexFlat
{
    /**
     * Table name
     */
    const TABLE_NAME = 'tooso_attributes_values_flat';

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
     * @throws \Exception
     */
    public function storeData($data, $storeId)
    {
        $updateTime = new \DateTime();
        $updateTimeStr = $updateTime->format('Y-m-d H:i:s');
        $data = array_map(function($item) use ($storeId, $updateTimeStr) {
            return [
                'store_id' => $storeId,
                'attribute_id' => isset($item['attribute_id']) ? $item['attribute_id'] : 'undefined',
                'value' => isset($item['value']) ? $item['value'] : 'undefined',
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
     */
    public function extractData($storeId)
    {
        $headers = ['attribute_id', 'value'];

        try {
            $tableName = $this->resource->getTableName(self::TABLE_NAME);
            $select = $this->connection->select()
                ->from($tableName, ['attribute_id', 'value'])
                ->where('store_id = ?', $storeId);
            $data = $this->connection->query($select)->fetchAll();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return null;
        }

        if (sizeof($data) === 0) {
            $this->logger->error('No data into '.self::TABLE_NAME.', reindex is required');
            return [];
        }

        return array_merge([$headers], $data);
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
