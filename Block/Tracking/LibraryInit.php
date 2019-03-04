<?php
namespace Bitbull\Tooso\Block\Tracking;

use Bitbull\Tooso\Api\Block\ScriptInterface;
use Bitbull\Tooso\Api\Service\Config\AnalyticsConfigInterface;
use Bitbull\Tooso\Api\Service\ConfigInterface;
use Bitbull\Tooso\Api\Service\TrackingInterface;
use Magento\Framework\View\Element\Template\Context;

class LibraryInit extends \Magento\Framework\View\Element\Template implements ScriptInterface
{
    const SCRIPT_ID = 'tooso-tracking-library-init';

    /**
     * @var AnalyticsConfigInterface
     */
    public $analyticsConfig;

    /**
     * @var ConfigInterface
     */
    public $config;

    /**
     * @var TrackingInterface
     */
    public $tracking;

    /**
     * LibraryInit constructor.
     *
     * @param Context $context
     * @param AnalyticsConfigInterface $analyticsConfig
     * @param ConfigInterface $config
     * @param TrackingInterface $tracking
     */
    public function __construct(
        Context $context,
        AnalyticsConfigInterface $analyticsConfig,
        ConfigInterface $config,
        TrackingInterface $tracking
    ) {
        parent::__construct($context);
        $this->analyticsConfig = $analyticsConfig;
        $this->config = $config;
        $this->tracking = $tracking;
    }

    /**
     * @inheritdoc
     */
    public function getScriptId()
    {
        return self::SCRIPT_ID;
    }
}
