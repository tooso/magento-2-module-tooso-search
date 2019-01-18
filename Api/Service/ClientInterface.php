<?php declare(strict_types=1);

namespace Bitbull\Tooso\Api\Service;

use \Tooso\SDK\ClientInterface as SDKClientInterface;

/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
interface ClientInterface
{
    /**
     * Get Tooso client
     *
     * @return SDKClientInterface
     */
    public function build();
}
