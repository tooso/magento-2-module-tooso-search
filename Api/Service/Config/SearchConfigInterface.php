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
     * Get supported order types
     *
     * @return array
     */
    public function getSupportedOrderTypes();

    /**
     * Get query params to exclude as filter
     *
     * @return array
     */
    public function getParamFilterExclusion();

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
