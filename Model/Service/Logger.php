<?php declare(strict_types=1);

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
     * @inheritdoc
     */
    public function log($message, $level = null, $context = [])
    {
        if ($level === null) {
            $level = Monolog::INFO;
        }
        $this->logger->log($level, $message, $context);
    }

    /**
     * @inheritdoc
     */
    public function logException(Exception $e)
    {
        $this->log($e->__toString(), Monolog::CRITICAL, $e->getTrace());
    }

    /**
     * @inheritdoc
     */
    public function debug($message, $context = [])
    {
        if ($this->config->isDebugModeEnabled()) {
            $this->log($message, Monolog::DEBUG, $context);
        }
    }

    /**
     * @inheritdoc
     */
    public function error($message, $context = [])
    {
        if ($this->config->isDebugModeEnabled()) {
            $this->log($message, Monolog::ERROR, $context);
        }
    }

    /**
     * @inheritdoc
     */
    public function warn($message, $context = [])
    {
        if ($this->config->isDebugModeEnabled()) {
            $this->log($message, Monolog::WARNING, $context);
        }
    }

    /**
     * @inheritdoc
     */
    public function info($message, $context = [])
    {
        if ($this->config->isDebugModeEnabled()) {
            $this->log($message, Monolog::INFO, $context);
        }
    }
}
