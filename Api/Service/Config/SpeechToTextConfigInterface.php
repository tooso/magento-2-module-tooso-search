<?php declare(strict_types=1);

namespace Bitbull\Tooso\Api\Service\Config;

/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
interface SpeechToTextConfigInterface
{
    /**
     * Get inpute selector
     *
     * @return string
     */
    public function getInputSelector();

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage();

    /**
     * Check if example template is enabled
     *
     * @return boolean
     */
    public function isExampleTemplateEnabled();
}
