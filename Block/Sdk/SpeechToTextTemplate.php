<?php
namespace Bitbull\Tooso\Block\Sdk;

use Bitbull\Tooso\Api\Block\ScriptInterface;
use Bitbull\Tooso\Api\Service\Config\SpeechToTextConfigInterface;
use Magento\Framework\View\Element\Template\Context;
use Bitbull\Tooso\Api\Service\ConfigInterface;

class SpeechToTextTemplate extends \Magento\Framework\View\Element\Template implements ScriptInterface
{
    const SCRIPT_ID = 'tooso-sdk-speech-to-text-template';

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var SpeechToTextConfigInterface
     */
    protected $speechToTextConfig;

    /**
     * LibraryInit constructor.
     *
     * @param Context $context
     * @param ConfigInterface $config
     * @param SpeechToTextConfigInterface $speechToTextConfig
     */
    public function __construct(
        Context $context,
        ConfigInterface $config,
        SpeechToTextConfigInterface $speechToTextConfig
    ) {
        parent::__construct($context);
        $this->config = $config;
        $this->speechToTextConfig = $speechToTextConfig;
    }

    /**
     * @inheritdoc
     */
    public function getScriptId()
    {
        return self::SCRIPT_ID;
    }

    /**
     * Is example template enabled
     *
     * @return boolean
     */
    public function isExampleTemplateEnabled()
    {
        return $this->speechToTextConfig->isExampleTemplateEnabled();
    }

    /**
     * Get input selector
     *
     * @return string
     */
    public function getInputSelector()
    {
        return $this->speechToTextConfig->getInputSelector();
    }

    /**
     * @inheritdoc
     */
    public function _toHtml()
    {
        if ($this->config->isSpeechToTextEnabled() === false || $this->isExampleTemplateEnabled() === false) {
            return '';
        }
        return parent::_toHtml();
    }

}
