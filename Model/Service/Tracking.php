<?php declare(strict_types=1);

namespace Bitbull\Tooso\Model\Service;

use Bitbull\Tooso\Api\Service\Config\AnalyticsConfigInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;
use Bitbull\Tooso\Api\Service\SessionInterface;
use Bitbull\Tooso\Api\Service\TrackingInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\HTTP\Header;
use Magento\Framework\UrlInterface;

class Tracking implements TrackingInterface
{
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
     * @var RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var Header
     */
    protected $httpHeader;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var AnalyticsConfigInterface
     */
    private $analyticsConfig;

    /**
     * Config constructor.
     *
     * @param LoggerInterface $logger
     * @param ProductMetadataInterface $productMetadata
     * @param SessionInterface $session
     * @param RemoteAddress $remoteAddress
     * @param Header $httpHeader
     * @param UrlInterface $url
     * @param AnalyticsConfigInterface $analyticsConfig
     */
    public function __construct(
        LoggerInterface $logger,
        ProductMetadataInterface $productMetadata,
        SessionInterface $session,
        RemoteAddress $remoteAddress,
        Header $httpHeader,
        UrlInterface $url,
        AnalyticsConfigInterface $analyticsConfig
    ) {
        $this->logger = $logger;
        $this->productMetadata = $productMetadata;
        $this->session = $session;
        $this->remoteAddress = $remoteAddress;
        $this->httpHeader = $httpHeader;
        $this->url = $url;
        $this->analyticsConfig = $analyticsConfig;
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
        $this->session->isLoggedIn();

        $params = [
            'uip' => $this->getRemoteAddr(),
            'ua' => $this->getUserAgent(),
            'sessionId' => $this->session->getSessionId(),
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
     * Get remote address
     *
     * @return string
     */
    public function getRemoteAddr()
    {
        return $this->remoteAddress->getRemoteAddress();
    }

    /**
     * Get client user agent
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->httpHeader->getHttpUserAgent();
    }

    /**
     * Get last page
     *
     * @return string
     */
    public function getLastPage()
    {
        return $this->httpHeader->getHttpReferer();
    }

    /**
     * Get current page
     *
     * @return string
     */
    public function getCurrentPage()
    {
        return $this->url->getCurrentUrl();
    }
}
