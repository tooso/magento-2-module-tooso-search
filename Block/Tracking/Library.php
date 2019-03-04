<?php
namespace Bitbull\Tooso\Block\Tracking;

use Bitbull\Tooso\Api\Block\ScriptInterface;
use Bitbull\Tooso\Api\Service\Config\AnalyticsConfigInterface;
use Bitbull\Tooso\Api\Service\ConfigInterface;
use Magento\Framework\View\Element\Template\Context;

class Library extends \Magento\Framework\View\Element\Template implements ScriptInterface
{
    const SCRIPT_ID = 'tooso-tracking-library';

    /**
     * @var AnalyticsConfigInterface
     */
    public $analyticsConfig;

    /**
     * @var ConfigInterface
     */
    public $config;

    /**
     * Library constructor.
     *
     * @param Context $context
     * @param AnalyticsConfigInterface $analyticsConfig
     * @param ConfigInterface $config
     */
    public function __construct(
        Context $context,
        AnalyticsConfigInterface $analyticsConfig,
        ConfigInterface $config
    ) {
        parent::__construct($context);
        $this->analyticsConfig = $analyticsConfig;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getScriptId()
    {
        return self::SCRIPT_ID;
    }
}
