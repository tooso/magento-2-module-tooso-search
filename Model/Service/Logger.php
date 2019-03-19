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
    public function log($message, $level = null)
    {
        if ($level === null) {
            $level = Monolog::INFO;
        }
        $this->logger->log($level, $message);
    }

    /**
     * @inheritdoc
     */
    public function logException(Exception $e)
    {
        $this->log($e->__toString(), Monolog::ERROR);
    }

    /**
     * @inheritdoc
     */
    public function debug($message)
    {
        if ($this->config->isDebugModeEnabled()) {
            $this->log($message, Monolog::DEBUG);
        }
    }

    /**
     * @inheritdoc
     */
    public function error($message)
    {
        if ($this->config->isDebugModeEnabled()) {
            $this->log($message, Monolog::ERROR);
        }
    }

    /**
     * @inheritdoc
     */
    public function warn($message)
    {
        if ($this->config->isDebugModeEnabled()) {
            $this->log($message, Monolog::ERROR);
        }
    }

    /**
     * @inheritdoc
     */
    public function info($message)
    {
        if ($this->config->isDebugModeEnabled()) {
            $this->log($message, Monolog::INFO);
        }
    }
}
