<?php declare(strict_types=1);

namespace Bitbull\Tooso\Api\Service;

use Tooso\SDK\Search\Result;
use Magento\Catalog\Ui\DataProvider\Product\ProductCollection;

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

    /**
     * Register search collection
     *
     * @param ProductCollection $collection
     */
    public function registerSearchCollection($collection);

    /**
     * Get search collection
     *
     * @return ProductCollection $collection
     */
    public function getSearchCollection();

}
