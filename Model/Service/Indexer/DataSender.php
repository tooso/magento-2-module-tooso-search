<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Service\Indexer;

use Bitbull\Tooso\Api\Service\Config\IndexerConfigInterface;
use Bitbull\Tooso\Api\Service\Indexer\DataSenderInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;
use Bitbull\Tooso\Model\Service\Indexer\Db\CatalogIndexFlat;
use Tooso\SDK\ClientBuilder;

class DataSender implements DataSenderInterface
{
    const CSV_SEPARATOR = ';';

    /**
     * @var IndexerConfigInterface
     */
    protected $indexerConfig;

    /**
     * @var ClientBuilder
     */
    protected $clientBuilder;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CatalogIndexFlat
     */
    protected $catalogIndexFlat;

    /**
     * @param IndexerConfigInterface $indexerConfig
     * @param LoggerInterface $logger
     * @param ClientBuilder $clientBuilder
     * @param CatalogIndexFlat $catalogIndexFlat
     */
    public function __construct(
        IndexerConfigInterface $indexerConfig,
        LoggerInterface $logger,
        ClientBuilder $clientBuilder,
        CatalogIndexFlat $catalogIndexFlat
    )
    {
        $this->indexerConfig = $indexerConfig;
        $this->logger = $logger;
        $this->clientBuilder = $clientBuilder;
        $this->catalogIndexFlat = $catalogIndexFlat;
    }

    /**
     * @inheritdoc
     */
    public function sendCatalog()
    {
        $storeIds = $this->indexerConfig->getStores();

        foreach ($storeIds as $storeId) {
            $data = $this->catalogIndexFlat->extractData($storeId);
            if ($data === null) {
                return false;
            }
            $csvContent = $this->arrayToCsv($data);

            $client = $this->getClient();
            try{
                $indexResult = $client->index($csvContent, [
                    'ACCESS_KEY_ID' => $this->indexerConfig->getAwsAccessKey(),
                    'SECRET_KEY' => $this->indexerConfig->getAwsSecretKey(),
                    'BUCKET' => $this->indexerConfig->getAwsBucketName(),
                    'PATH' => $this->indexerConfig->getAwsBucketPath(),
                ]);
                if ($indexResult->isValid() === false) {
                    return false;
                }
            }catch (\Tooso\SDK\Exception $e){
                $this->logger->error($e->getMessage());
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function sendStock()
    {
        // TODO: Implement sendStock() method.
    }

    /**
     * Get Client
     *
     * @return \Tooso\SDK\Client
     */
    protected function getClient()
    {
        return $this->clientBuilder
            ->withLogger($this->logger)
            ->build();
    }

    /**
     * Convert array into CSV value
     *
     * @param array $array
     * @return string
     */
    protected function arrayToCsv($array)
    {
        $dataString = '';
        foreach ($array as $row) {
            $dataStringRow = '';
            foreach ($row as $column) {
                $dataStringRow .= '"'.$column.'"'.self::CSV_SEPARATOR;
            }
            $dataString .= $dataStringRow.PHP_EOL;
        }
        return $dataString;
    }
}
