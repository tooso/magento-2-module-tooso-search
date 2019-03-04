<?php declare(strict_types=1);

namespace Bitbull\Tooso\Api\Service\Config;

/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
interface AnalyticsConfigInterface
{
    /**
     * Get cookie domain
     *
     * @param string $default
     * @return array
     */
    public function getCookieDomain($default = null);

    /**
     * Get library endpoint
     *
     * @return string|null
     */
    public function getLibraryEndpoint();

    /**
     * Get API endpoint
     *
     * @return string|null
     */
    public function getAPIEndpoint();

    /**
     * Get API version
     *
     * @return string|null
     */
    public function getAPIVersion();

    /**
     * Get Analytics key
     *
     * @return string|null
     */
    public function getKey();

    /**
     * Is library included
     *
     * @return boolean
     */
    public function isLibraryIncluded();

    /**
     * Is in debug mode
     *
     * @return boolean
     */
    public function isDebugMode();

    /**
     * Is customer users tracking enabled
     *
     * @return boolean
     */
    public function isUserIdTrackingEnable();
}
