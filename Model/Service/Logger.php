<?php

namespace Bitbull\Tooso\Model\Service;

use Bitbull\Tooso\Api\Service\LoggerInterface;
use Bitbull\Tooso\Api\Service\ConfigInterface;
use Tooso\SDK\Exception;
use Magento\Framework\Logger\Monolog;

class Logger implements LoggerInterface
{
    /**
     * @var Monolog|null
     */
    protected $logger = null;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @param Monolog $logger
     * @param ConfigInterface $config
     */
    public function __construct(Monolog $logger, ConfigInterface $config)
    {
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * Logging facility
     *
     * @param string $message
     * @param int $level
     */
    public function log($message, $level = null)
    {
        if ($level === null) {
            $level = Monolog::INFO;
        }
        $this->logger->log($level, $message);
    }

    /**
     * @param Exception $e
     */
    public function logException(Exception $e)
    {
        $this->log($e->__toString(), Monolog::ERROR);
    }

    /**
     * @param string $message
     */
    public function debug($message)
    {
        if ($this->config->isDebugModeEnabled()) {
            $this->log($message, Monolog::DEBUG);
        }
    }
}