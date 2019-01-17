<?php

namespace Bitbull\Tooso\Api\Service;

/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
interface TrackingInterface
{
    /**
     * Get tracking API user agent
     *
     * @return string
     */
    public function getApiAgent();

    /**
     * Get current installed module version
     *
     * @return string
     */
    public function getModuleVersion();

    /**
     * Get profiling params to identify caller
     *
     * @param array $override
     * @return array
     */
    public function getProfilingParams($override = null);

    /**
     * Get client IP
     *
     * @return string
     */
    public function getRemoteAddr();

    /**
     * Get client user agent
     *
     * @return string
     */
    public function getUserAgent();

    /**
     * Get last page
     *
     * @return string
     */
    public function getLastPage();

    /**
     * Get current page
     *
     * @return string
     */
    public function getCurrentPage();
}