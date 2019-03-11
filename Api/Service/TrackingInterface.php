<?php declare(strict_types=1);

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
     * Get current PHP version
     *
     * @return string
     */
    public function getPHPVersion();

    /**
     * Get current Magento version
     *
     * @return string
     */
    public function getMagentoVersion();

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
     * Get product tracking params
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return null|array
     */
    public function getProductTrackingParams($product);

    /**
     * Get order tracking params
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return null|array
     */
    public function getOrderTrackingParams($order);

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

    /**
     * Get currency code
     *
     * @return string
     */
    public function getCurrencyCode();

    /**
     * Get currency code
     *
     * @param array $params
     * @return boolean
     */
    public function executeTrackingRequest($params);
}
