<?php

namespace Bitbull\Tooso\Api\Service;

/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
interface SearchConfigInterface
{
    /**
     * Get API Key
     *
     * @return string
     */
    public function getApiKey();

    /**
     * Get API Version
     *
     * @return string
     */
    public function getApiVersion();

    /**
     * Get API base URL
     *
     * @return string
     */
    public function getApiBaseUrl();
}