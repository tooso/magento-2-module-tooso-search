<?php declare(strict_types=1);

namespace Bitbull\Tooso\Api\Service;

use Tooso\SDK\Search\Result;
use Tooso\SDK\Exception;

/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
interface SearchInterface
{
    /**
     * Execute Tooso search
     *
     * @param string $query
     * @param integer $page
     * @param integer $limit
     * @return Result
     */
    public function execute($query, $page = null, $limit = null);

    /**
     * Extract product ids and score from Tooso response
     *
     * @return array
     */
    public function getProducts();

    /**
     * Retrive parent search ID from request param
     *
     * @return string
     */
    public function getParentSearchId();

    /**
     * Is a typo corrected search
     *
     * @param string $queryParam
     * @param string $parentSearchId
     * @return string
     */
    public function getSearchUrl($queryParam, $parentSearchId);

    /**
     * Is a typo corrected search
     *
     * @return bool
     */
    public function isTypoCorrectedSearch();

    /**
     * Is search fallback enable
     *
     * @return bool
     */
    public function isFallbackEnable();
}
