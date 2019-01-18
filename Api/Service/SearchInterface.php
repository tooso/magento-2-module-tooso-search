<?php declare(strict_types=1);

namespace Bitbull\Tooso\Api\Service;

use \Tooso\SDK\Search\Result;

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
     * @param boolean $typoCorrection
     * @return Result
     */
    public function execute($query, $typoCorrection = true);
}
