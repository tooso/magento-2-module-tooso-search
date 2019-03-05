<?php
namespace Bitbull\Tooso\Block\Tracking;

use Bitbull\Tooso\Api\Block\ScriptInterface;
use Bitbull\Tooso\Api\Service\Config\AnalyticsConfigInterface;
use Bitbull\Tooso\Api\Service\ConfigInterface;
use Bitbull\Tooso\Api\Service\SessionInterface;
use Magento\Framework\View\Element\Template\Context;

class CustomerTracking extends \Magento\Framework\View\Element\Template implements ScriptInterface
{
    const SCRIPT_ID = 'tooso-tracking-customer';

    /**
     * @var AnalyticsConfigInterface
     */
    protected $analyticsConfig;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * LibraryInit constructor.
     *
     * @param Context $context
     * @param AnalyticsConfigInterface $analyticsConfig
     * @param ConfigInterface $config
     * @param SessionInterface $session
     */
    public function __construct(
        Context $context,
        AnalyticsConfigInterface $analyticsConfig,
        ConfigInterface $config,
        SessionInterface $session
    ) {
        parent::__construct($context);
        $this->analyticsConfig = $analyticsConfig;
        $this->config = $config;
        $this->session = $session;

        // Cache based on customer session
        $this->_isScopePrivate = true;
    }

    /**
     * @inheritdoc
     */
    public function getScriptId()
    {
        return self::SCRIPT_ID;
    }

    /**
     * Check if user is logged in
     *
     * @return boolean
     */
    public function isLoggedIn()
    {
        return $this->session->isLoggedIn();
    }

    /**
     * Get Magento customer ID
     *
     * @return integer
     */
    public function getCustomerId()
    {
        return $this->session->getCustomerId();
    }

    /**
     * @inheritdoc
     */
    public function _toHtml()
    {
        if ($this->analyticsConfig->isUserIdTrackingEnable() === false) {
            return '';
        }
        return parent::_toHtml();
    }
}
