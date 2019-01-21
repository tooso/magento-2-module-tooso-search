<?php declare(strict_types=1);

namespace Bitbull\Tooso\Api\Service\Config;

/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
interface SearchConfigInterface
{
    /**
     * Get default limit
     *
     * @return int
     */
    public function getDefaultLimit();

    /**
     * Is search response enriched
     *
     * @return boolean
     */
    public function isEnriched();

    /**
     * Is Magento search fallback enable
     *
     * @return boolean
     */
    public function isFallbackEnable();
}
