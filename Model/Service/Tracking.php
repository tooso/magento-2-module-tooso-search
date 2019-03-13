<?php declare(strict_types=1);

namespace Bitbull\Tooso\Model\Service;

use Bitbull\Tooso\Api\Service\ConfigInterface;
use Bitbull\Tooso\Api\Service\Config\AnalyticsConfigInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;
use Bitbull\Tooso\Api\Service\SessionInterface;
use Bitbull\Tooso\Api\Service\TrackingInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\HTTP\Header;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Tooso\SDK\ClientBuilder;
use Tooso\SDK\Client;
use Tooso\SDK\Exception;

class Tracking implements TrackingInterface
{
    /**
     * Tracking request timeout
     * @var integer
     */
    const TRACKING_REQUEST_TIMEOUT = 1000;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var RequestHttp
     */
    protected $request;

    /**
     * @var Header
     */
    protected $httpHeader;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var AnalyticsConfigInterface
     */
    private $analyticsConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryProductRepository;

    /**
     * @var ClientBuilder
     */
    protected $clientBuilder;

    /**
     * Config constructor.
     *
     * @param LoggerInterface $logger
     * @param ProductMetadataInterface $productMetadata
     * @param SessionInterface $session
     * @param RequestHttp $request
     * @param Header $httpHeader
     * @param UrlInterface $url
     * @param ConfigInterface $config
     * @param AnalyticsConfigInterface $analyticsConfig
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryRepositoryInterface $categoryProductRepository
     * @param ClientBuilder $clientBuilder
     */
    public function __construct(
        LoggerInterface $logger,
        ProductMetadataInterface $productMetadata,
        SessionInterface $session,
        RequestHttp $request,
        Header $httpHeader,
        UrlInterface $url,
        ConfigInterface $config,
        AnalyticsConfigInterface $analyticsConfig,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryProductRepository,
        ClientBuilder $clientBuilder
    ) {
        $this->logger = $logger;
        $this->productMetadata = $productMetadata;
        $this->session = $session;
        $this->request = $request;
        $this->httpHeader = $httpHeader;
        $this->url = $url;
        $this->config = $config;
        $this->analyticsConfig = $analyticsConfig;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->categoryProductRepository = $categoryProductRepository;
        $this->clientBuilder = $clientBuilder;
    }

    /**
     * @inheritdoc
     */
    public function getModuleVersion()
    {
        $version = 'undefined';
        try {
            $reflection = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
            $vendorDir = dirname(dirname($reflection->getFileName()));
            $packages = json_decode(file_get_contents($vendorDir . '/composer/installed.json'), true);
            foreach ($packages as $package) {
                if ($package['name'] === 'bitbull/magento-2-tooso-search') {
                    $version = $package['version'];
                    break;
                }
            }
        } catch (\Exception $e) {
            $version = 'error: ' . $e->getMessage();
        }
        return $version;
    }

    /**
     * @inheritdoc
     */
    public function getPHPVersion()
    {
        return PHP_VERSION;
    }

    /**
     * @inheritdoc
     */
    public function getMagentoVersion()
    {
        return $this->productMetadata->getVersion();
    }

    /**
     * @inheritdoc
     */
    public function getApiAgent()
    {
        return implode(' ', [
            'PHP/'.$this->getPHPVersion(),
            'Magento/'.$this->getMagentoVersion(),
            'Tooso/'.$this->getModuleVersion(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getProfilingParams($override = null)
    {
        $params = [
            'uip' => $this->getRemoteAddr(),
            'ua' => $this->getUserAgent(),
            'cid' => $this->session->getClientId(),
            'dr' => $this->getLastPage(),
            'dl' => $this->getCurrentPage(),
            'tm' => round(microtime(true) * 1000)
        ];

        if ($this->analyticsConfig->isUserIdTrackingEnable() && $this->session->isLoggedIn()) {
            $params['uid'] = $this->session->getCustomerId();
        }

        if ($override !== null && is_array($override)) {
            $params = array_merge($params, $override);
        }

        return $params;
    }

    /**
     * @inheritdoc
     */
    public function getProductTrackingParams($product)
    {
        $trackingProductParams = [
            'id' => $product->getSku(),
            'name' => $product->getName(),
            'brand' => $product->getManufacturer(),
            'price' => $product->getFinalPrice(),
            'quantity' => 1,
            'position' => 0,
        ];

        $categoryIds = $product->getCategoryIds();
        if(count($categoryIds) > 0){
            $currentProductCategory = $this->categoryProductRepository->get($categoryIds[0]);
            $trackingProductParams['category'] = $currentProductCategory->getName();
        }else{
            $trackingProductParams['category'] = null;
        }

        return $trackingProductParams;
    }

    /**
     * @inheritdoc
     */
    public function getOrderTrackingParams($order)
    {
        return [
            'id' => $order->getId(),
            'shipping' => $order->getShippingAmount(),
            'coupon' => $order->getCouponCode(),
            'tax' => $order->getTaxAmount(),
            'revenue' => $order->getGrandTotal(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getRemoteAddr()
    {
        return $this->request->getClientIp(true);
    }

    /**
     * @inheritdoc
     */
    public function getUserAgent()
    {
        return $this->httpHeader->getHttpUserAgent();
    }

    /**
     * @inheritdoc
     */
    public function getLastPage()
    {
        return $this->httpHeader->getHttpReferer();
    }

    /**
     * @inheritdoc
     */
    public function getCurrentPage()
    {
        return $this->url->getCurrentUrl();
    }

    /**
     * @inheritdoc
     */
    public function getCurrencyCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * @inheritdoc
     */
    public function executeTrackingRequest($params)
    {
        $profilingParams = $this->getProfilingParams();
        $params = array_merge([
            'z' => $this->getUuid(),
            'tid' => $this->analyticsConfig->getKey(),
            'v' => $this->analyticsConfig->getAPIVersion(),
        ], $profilingParams, $params);

        $client = $this->getClient();
        try{
            $client->doRequest('collect', Client::HTTP_METHOD_GET, $params, self::TRACKING_REQUEST_TIMEOUT, true);
        }catch (Exception $e){
            $this->logger->logException($e);
            return false;
        }

        return true;
    }

    /**
     * Generate new client ID
     *
     * @return string
     */
    protected function getUuid()
    {
        return $this->clientBuilder->build()->getUuid();
    }

    /**
     * Get Client
     *
     * @return \Tooso\SDK\Client
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getClient()
    {
        return $this->clientBuilder
            ->withApiKey($this->config->getApiKey())
            ->withApiBaseUrl($this->analyticsConfig->getAPIEndpoint())
            ->withAgent($this->getApiAgent())
            ->withLogger($this->logger)
            ->build();
    }
}
