<?php declare(strict_types=1);

namespace Bitbull\Tooso\Model\Service\Config;

use Bitbull\Tooso\Api\Service\Config\SpeechToTextConfigInterface;
use Bitbull\Tooso\Api\Service\Config\SuggestionConfigInterface;
use Bitbull\Tooso\Api\Service\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class SpeechToTextConfig implements SpeechToTextConfigInterface
{
    const XML_PATH_SPEECH_TO_TEXT_INPUT_SELECTOR = 'tooso/speech_to_text/input_selector';
    const XML_PATH_SPEECH_TO_TEXT_LANGUAGE = 'tooso/speech_to_text/language';
    const XML_PATH_SPEECH_TO_TEXT_EXAMPLE_TEMPLATE = 'tooso/speech_to_text/example_template';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var SuggestionConfigInterface
     */
    protected $suggestionConfig;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigInterface $config
     * @param SuggestionConfigInterface $suggestionConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ConfigInterface $config,
        SuggestionConfigInterface $suggestionConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
        $this->suggestionConfig = $suggestionConfig;
    }

    /**
     * @inheritdoc
     */
    public function getInputSelector()
    {
        $inputSelector = $this->scopeConfig->getValue(self::XML_PATH_SPEECH_TO_TEXT_INPUT_SELECTOR);
        if ($inputSelector === null) {
            return $this->suggestionConfig->getInputSelector();
        }
        return $inputSelector;
    }

    /**
     * @inheritdoc
     */
    public function getLanguage()
    {
        $inputSelector = $this->scopeConfig->getValue(self::XML_PATH_SPEECH_TO_TEXT_LANGUAGE);
        if ($inputSelector === null) {
            return $this->config->getLanguage();
        }
        return $inputSelector;
    }

    /**
     * @inheritdoc
     */
    public function isExampleTemplateEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SPEECH_TO_TEXT_EXAMPLE_TEMPLATE);
    }

}
