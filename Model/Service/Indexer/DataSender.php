<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Service\Indexer;

use Bitbull\Tooso\Api\Service\Config\IndexerConfigInterface;
use Bitbull\Tooso\Api\Service\ConfigInterface;
use Bitbull\Tooso\Api\Service\Indexer\DataSenderInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;
use Bitbull\Tooso\Model\Service\Indexer\Db\AttributesValuesIndexFlat;
use Bitbull\Tooso\Model\Service\Indexer\Db\CatalogIndexFlat;
use Bitbull\Tooso\Model\Service\Indexer\Db\StockIndexFlat;
use Tooso\SDK\ClientBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class DataSender implements DataSenderInterface
{
    const CSV_SEPARATOR = ',';

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
     * @var AttributesValuesIndexFlat
     */
    protected $attributesValuesIndexFlat;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param ConfigInterface $config
     * @param IndexerConfigInterface $indexerConfig
     * @param LoggerInterface $logger
     * @param ClientBuilder $clientBuilder
     * @param CatalogIndexFlat $catalogIndexFlat
     * @param StockIndexFlat $stockIndexFlat
     * @param AttributesValuesIndexFlat $attributesValuesIndexFlat
     * @param StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     */
    public function __construct(
        ConfigInterface $config,
        IndexerConfigInterface $indexerConfig,
        LoggerInterface $logger,
        ClientBuilder $clientBuilder,
        CatalogIndexFlat $catalogIndexFlat,
        StockIndexFlat $stockIndexFlat,
        AttributesValuesIndexFlat $attributesValuesIndexFlat,
        StoreManagerInterface $storeManager,
        Filesystem $filesystem
    )
    {
        $this->config = $config;
        $this->indexerConfig = $indexerConfig;
        $this->logger = $logger;
        $this->clientBuilder = $clientBuilder;
        $this->catalogIndexFlat = $catalogIndexFlat;
        $this->stockIndexFlat = $stockIndexFlat;
        $this->attributesValuesIndexFlat = $attributesValuesIndexFlat;
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
    }

    /**
     * @inheritdoc
     */
    public function sendCatalog()
    {
        $storeIds = $this->indexerConfig->getStores();

        $this->logger->info('[catalog export] Start exporting data for stores ' . implode(',', $storeIds) . '..');

        $headers = $this->indexerConfig->getAttributes();
        array_unshift($headers, 'id');
        sort($headers);

        foreach ($storeIds as $storeId) {
            $this->storeManager->setCurrentStore($storeId);

            $this->logger->debug("[catalog export] Start exporting catalog data for store $storeId");

            // Export catalog


            $catalogData = $this->catalogIndexFlat->extractData($storeId, $headers);
            if ($catalogData === null) {
                $this->logger->warn("[catalog export] An error occurred during store $storeId catalog data extract, skipping..");
                continue;
            }
            $catalogCsvContent = $this->arrayToCsv($catalogData);

            // Export attributes

            $attributesData = $this->attributesValuesIndexFlat->extractData($storeId);
            if ($attributesData === null) {
                $this->logger->warn("[catalog export] An error occurred during store $storeId attributes data extract, skipping..");
                continue;
            }
            $attributeCsvContent = $this->arrayToCsv($attributesData);

            if ($this->indexerConfig->isDryRunModeEnabled()) {
                $this->writeCsvToFile($catalogCsvContent, $storeId, 'catalog_');
                $this->writeCsvToFile($attributeCsvContent, $storeId, 'attributes_');
                continue;
            }

            $client = $this->getClient();
            try{
                $indexResult = $client->index([
                    'magento_catalog.csv' => $catalogCsvContent,
                    'magento_attributes.csv' => $attributeCsvContent
                ], [
                    'ACCESS_KEY_ID' => $this->indexerConfig->getAwsAccessKey(),
                    'SECRET_KEY' => $this->indexerConfig->getAwsSecretKey(),
                    'BUCKET' => $this->indexerConfig->getAwsBucketName(),
                    'PATH' => $this->indexerConfig->getAwsBucketPath(),
                ]);
                if ($indexResult->isValid() === false) {
                    $this->logger->error($indexResult->getErrorMessage());
                    return false;
                }
            }catch (\Tooso\SDK\Exception $e){
                $this->logger->error($e->getMessage());
                return false;
            }

            $this->logger->debug("[catalog export] Store $storeId catalog data successfully exported!");
        }

        $this->logger->info('[catalog export] All stores catalog data successfully exported!');
        return true;
    }

    /**
     * @inheritdoc
     */
    public function sendStock()
    {
        $storeIds = $this->indexerConfig->getStores();

        $this->logger->info('[stock export] Start exporting stock data for stores ' . implode(',', $storeIds) . '..');

        foreach ($storeIds as $storeId) {
            $this->logger->debug("[stock export] Start exporting catalog data for store $storeId");

            $data = $this->stockIndexFlat->extractData(); //NOTE: product's stock values are global
            if ($data === null) {
                return false;
            }
            $csvContent = $this->arrayToCsv($data);

            if ($this->indexerConfig->isDryRunModeEnabled()) {
                $this->writeCsvToFile($csvContent, $storeId, 'stock_');
                continue;
            }

            $client = $this->getClient();
            try{
                $indexResult = $client->index($csvContent, [
                    'ACCESS_KEY_ID' => $this->indexerConfig->getAwsAccessKey(),
                    'SECRET_KEY' => $this->indexerConfig->getAwsSecretKey(),
                    'BUCKET' => $this->indexerConfig->getAwsBucketName(),
                    'PATH' => $this->indexerConfig->getAwsBucketPath(),
                ]);
                if ($indexResult->isValid() === false) {
                    $this->logger->error($indexResult->getErrorMessage());
                    return false;
                }
            }catch (\Tooso\SDK\Exception $e){
                $this->logger->error($e->getMessage());
                return false;
            }

            $this->logger->debug("[stock export] Store $storeId stock data successfully exported!");
        }

        $this->logger->info('[stock export] All stores stock data successfully exported!');

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
        $csvContent = rtrim($csvContent);
        return mb_convert_encoding($csvContent, 'UTF-8', mb_detect_encoding($csvContent, 'UTF-8, ISO-8859-1', true));
    }

    /**
     * Write CSV file into var directory
     *
     * @param string $csvContent
     * @param integer $storeId
     * @param string $prefix
     */
    protected function writeCsvToFile($csvContent, $storeId, $prefix = '')
    {
        try {
            $varDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
            $varDirectory->writeFile("tooso/${prefix}${storeId}.csv", $csvContent);
        }catch (\Magento\Framework\Exception\FileSystemException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
