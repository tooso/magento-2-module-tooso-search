<?php declare(strict_types=1);

namespace Bitbull\Tooso\Api\Service;

use \Tooso\SDK\Storage\SessionInterface as SDKSessionInterface;

/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
interface SessionInterface extends SDKSessionInterface
{
    /**
     * Get Client ID from cookie
     *
     * @return string
     */
    public function getClientId();

    /**
     * Get session ID
     *
     * @return string
     */
    public function getSessionId();

    /**
     * Check if user is logged in
     *
     * @return boolean
     */
    public function isLoggedIn();
}
