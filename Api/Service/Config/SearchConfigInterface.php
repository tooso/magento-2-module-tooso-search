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
     * Is search response enriched
     *
     * @return boolean
     */
    public function isEnriched();
}
