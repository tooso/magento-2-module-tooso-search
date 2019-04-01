<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Logger;

use Magento\Framework\Logger\Handler\Base;
use Magento\Framework\Filesystem\DriverInterface;

class Handler extends Base
{
    /**
     * Log file name
     *
     * @var string
     */
    const LOG_FILE_NAME = 'tooso.log';

    /**
     * Logging level
     * @var int
     */
    protected $loggerType = \Monolog\Logger::DEBUG;

    /**
     * @param DriverInterface $filesystem
     * @param string $filePath
     * @param string $fileName
     */
    public function __construct(
        DriverInterface $filesystem,
        $filePath = null,
        $fileName = null
    ) {

        $this->fileName = '/var/log/'.self::LOG_FILE_NAME;

        parent::__construct($filesystem, $filePath, $fileName);
    }
}
