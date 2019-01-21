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
     * @return array
     */
    public function getCookieDomain();

    /**
     * Is customer users tracking enabled
     *
     * @return boolean
     */
    public function isUserIdTrackingEnable();
}
