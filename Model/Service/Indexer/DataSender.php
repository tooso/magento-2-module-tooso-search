<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Service\Indexer;

use Bitbull\Tooso\Api\Service\Config\IndexerConfigInterface;
use Bitbull\Tooso\Api\Service\ConfigInterface;
use Bitbull\Tooso\Api\Service\Indexer\DataSenderInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;
use Bitbull\Tooso\Model\Service\Indexer\Db\CatalogIndexFlat;
use Bitbull\Tooso\Model\Service\Indexer\Db\StockIndexFlat;
use Tooso\SDK\ClientBuilder;

class DataSender implements DataSenderInterface
{
    const CSV_SEPARATOR = ';';

    /**
     * @var ConfigInterface
     */
    protected $config;

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
     * @var StockIndexFlat
     */
    protected $stockIndexFlat;

    /**
     * @param ConfigInterface $config
     * @param IndexerConfigInterface $indexerConfig
     * @param LoggerInterface $logger
     * @param ClientBuilder $clientBuilder
     * @param CatalogIndexFlat $catalogIndexFlat
     * @param StockIndexFlat $stockIndexFlat
     */
    public function __construct(
        ConfigInterface $config,
        IndexerConfigInterface $indexerConfig,
        LoggerInterface $logger,
        ClientBuilder $clientBuilder,
        CatalogIndexFlat $catalogIndexFlat,
        StockIndexFlat $stockIndexFlat
    )
    {
        $this->config = $config;
        $this->indexerConfig = $indexerConfig;
        $this->logger = $logger;
        $this->clientBuilder = $clientBuilder;
        $this->catalogIndexFlat = $catalogIndexFlat;
        $this->stockIndexFlat = $stockIndexFlat;
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
        $storeIds = $this->indexerConfig->getStores();

        foreach ($storeIds as $storeId) {
            $data = $this->stockIndexFlat->extractData($storeId);
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
     * Get Client
     *
     * @return \Tooso\SDK\Client
     */
    protected function getClient()
    {
        return $this->clientBuilder
            ->withApiKey($this->config->getApiKey())
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
        $f = fopen('php://memory', 'rb+');
        foreach ($array as $fields) {
            fputcsv($f, $fields, self::CSV_SEPARATOR);
        }
        rewind($f);
        $csvContent = stream_get_contents($f);
        fclose($f);
        return rtrim($csvContent);
    }
}
