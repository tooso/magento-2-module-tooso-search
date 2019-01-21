<?php declare(strict_types=1);

namespace Bitbull\Tooso\Model\Service;

use Bitbull\Tooso\Api\Service\Config\AnalyticsConfigInterface;
use Bitbull\Tooso\Api\Service\SessionInterface;
use Bitbull\Tooso\Api\Service\TrackingInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Tooso\SDK\ClientBuilder;

class Session implements SessionInterface
{
    /**
     * @var string
     */
    const COOKIE_SEARCHID = 'ToosoSearchId';

    /**
     * @var string
     */
    const COOKIE_USERID = '_ta';

    /**
     * CookieManager
     *
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var SessionManagerInterface
     */
    protected $sessionManager;

    /**
     * @var CustomerSession
     */
    protected $session;

    /**
     * @var AnalyticsConfigInterface
     */
    private $analyticsConfig;

    /**
     * @var TrackingInterface
     */
    protected $tracking;

    /**
     * @var ClientBuilder
     */
    protected $clientBuilder;


    /**
     * @param SessionManagerInterface $sessionManager
     * @param CustomerSession $session,
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param AnalyticsConfigInterface $analyticsConfig
     */
    public function __construct(
        SessionManagerInterface $sessionManager,
        CustomerSession $session,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        AnalyticsConfigInterface $analyticsConfig
    ) {
        $this->sessionManager = $sessionManager;
        $this->session = $session;
        $this->analyticsConfig = $analyticsConfig;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->clientBuilder = new ClientBuilder();
    }

    /**
     * @inheritdoc
     */
    public function setSearchId($value)
    {
        $this->cookieManager->setPublicCookie(
            self::COOKIE_SEARCHID,
            $value,
            $this->cookieMetadataFactory
                ->createPublicCookieMetadata()
                ->setDurationOneYear()
                ->setHttpOnly(true)
                ->setPath($this->sessionManager->getCookiePath())
                ->setDomain($this->sessionManager->getCookieDomain())
        );
    }

    /**
     * @inheritdoc
     */
    public function getSearchId()
    {
        return $this->cookieManager->getCookie(self::COOKIE_SEARCHID);
    }

    /**
     * @inheritdoc
     */
    public function getClientId()
    {
        $cid = $this->cookieManager->getCookie(self::COOKIE_USERID);
        if($cid === false || $cid === ''){
            $cid = $this->_generateClientId();
            $domain = $this->analyticsConfig->getCookieDomain();
            $this->cookieManager->setPublicCookie(
                self::COOKIE_USERID,
                $cid,
                $this->cookieMetadataFactory
                    ->createPublicCookieMetadata()
                    ->setHttpOnly(false)
                    ->setDuration(63072000)
                    ->setPath('/')
                    ->setDomain($domain)
            );
        }

        return substr($cid ?? '', -36);
    }

    /**
     * @inheritdoc
     */
    public function isLoggedIn()
    {
        return $this->session->isLoggedIn();
    }

    /**
     * @inheritdoc
     */
    public function getSessionId()
    {
        return $this->session->getId();
    }

    /**
     * Generate new client ID
     *
     * @return string
     */
    private function _generateClientId()
    {
        $uuid = $this->clientBuilder->build()->getUuid();
        return 'TA.'.$uuid; //TODO: use constant
    }
}
