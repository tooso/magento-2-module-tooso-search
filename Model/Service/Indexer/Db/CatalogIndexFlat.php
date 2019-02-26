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
     * Insert multiple
     *
     * @param array $data
     * @param integer $storeId
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
        }
    }
}
