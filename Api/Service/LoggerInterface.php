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
     * Logging facility
     *
     * @param string $message
     * @param int $level
     * @param array $context
     */
    public function log($message, $level = null, $context = []);

    /**
     * @param string $message
     * @param array $context
     */
    public function debug($message, $context = []);

    /**
     * @param string $message
     * @param array $context
     */
    public function error($message, $context = []);

    /**
     * @param string $message
     * @param array $context
     */
    public function warn($message, $context = []);

    /**
     * @param string $message
     * @param array $context
     */
    public function info($message, $context = []);
}
