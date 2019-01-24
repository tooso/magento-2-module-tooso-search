<?php declare(strict_types=1);

namespace Bitbull\Tooso\Api\Service\Search;

/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
interface RequestParserInterface
{
    /**
     * Retrieve parent search ID from request param
     *
     * @return string
     */
    public function getParentSearchId();

    /**
     * Retrieve query parameter
     *
     * @return string
     */
    public function getQueryText();

    /**
     * Retrieve filter parameter
     *
     * @return string|null
     */
    public function getFilterParam();

    /**
     * Retrieve order parameter
     *
     * @return string|null
     */
    public function getOrderParam();

    /**
     * Is a typo corrected search
     *
     * @param string $queryParam
     * @param string $parentSearchId
     * @return string
     */
    public function buildSearchUrl($queryParam, $parentSearchId);

    /**
     * Is a typo corrected search
     *
     * @return bool
     */
    public function isTypoCorrectedSearch();

    /**
     * Is sort parameter handled by Tooso
     *
     * @return bool
     */
    public function isSortHandled();

    /**
     * Are filters parameter handled by Tooso
     *
     * @return bool
     */
    public function areFiltersHandled();
}