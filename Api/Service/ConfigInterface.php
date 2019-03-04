<?php declare(strict_types=1);

namespace Bitbull\Tooso\Api\Service;

/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
interface ConfigInterface
{
    /**
     * Get current language
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLanguage();

    /**
     * Get current store code
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreCode();

    /**
     * Get API key
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

    /**
     * Is debug mode active
     *
     * @return boolean
     */
    public function isDebugModeEnabled();

    /**
     * Is search service active
     *
     * @return boolean
     */
    public function isSearchEnabled();

    /**
     * Is tracking service active
     *
     * @return boolean
     */
    public function isConfigEnabled();
}
