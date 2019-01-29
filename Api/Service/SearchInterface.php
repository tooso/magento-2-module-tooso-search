<?php declare(strict_types=1);

namespace Bitbull\Tooso\Api\Service;

use Tooso\SDK\Search\Result;

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
     * @param string $queryText
     * @return Result
     */
    public function execute($queryText = null);

    /**
     * Get last result
     *
     * @return Result
     */
    public function getResult();

    /**
     * Extract product ids and score from Tooso response
     *
     * @return array
     */
    public function getProducts();

    /**
     * Is search fallback enable
     *
     * @return bool
     */
    public function isFallbackEnable();
}
