<?php declare(strict_types=1);

namespace Bitbull\Tooso\Api\Service;

use \Tooso\SDK\Log\LoggerInterface as SDKLoggerInterface;

/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
interface LoggerInterface extends SDKLoggerInterface
{
    /**
     * @param string $message
     */
    public function error($message);

    /**
     * @param string $message
     */
    public function warn($message);

    /**
     * @param string $message
     */
    public function info($message);
}
