<?php declare(strict_types=1);

namespace Bitbull\Tooso\Api\Service\Config;

/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
interface SdkConfigInterface
{
    /**
     * Get library endpoint
     *
     * @return string
     */
    public function getLibraryEndpoint();

    /**
     * Get core key
     *
     * @return string
     */
    public function getCoreKey();

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
     * Check if debug mode is enabled
     *
     * @return boolean
     */
    public function isDebugModeEnabled();
}
