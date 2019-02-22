<?php declare(strict_types=1);

namespace Bitbull\Tooso\Api\Service\Config;

/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
interface IndexerConfigInterface
{
    /**
     * Get stores to index
     *
     * @return array
     */
    public function getStores();
    
    /**
     * Get attributes to export
     *
     * @return array
     */
    public function getAttributes();

}
