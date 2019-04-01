<?php
namespace Bitbull\Tooso\Controller\Adminhtml\System;

use Bitbull\Tooso\Model\Logger\Handler;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Bitbull\Tooso\Api\Service\LoggerInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class DownloadLog extends Action
{
    const PERMISSION_RESOURCE = 'Bitbull_Tooso::download_log';

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     * @param Filesystem $filesystem
     * @param DirectoryList $directoryList
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        Filesystem $filesystem,
        DirectoryList $directoryList
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->filesystem = $filesystem;
        $this->directoryList = $directoryList;
        parent::__construct($context);
    }

    /**
     * Collect relations data
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $this->logger->debug('[Download log] Requested log from download button..');

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();
        $content = '';
        try {
            $logPath = $this->filesystem->getDirectoryWrite($this->directoryList::LOG);
            $content = $logPath->readFile(Handler::LOG_FILE_NAME);
        } catch (FileSystemException $e) {
            $this->logger->error($e->getMessage());
            return $result->setHttpResponseCode(500);
        }
        $this->logger->debug('[Download log] Sent!');

        return $result->setData(['status' => 'ok', 'contentToDownload' => $content]);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return parent::_isAllowed() && $this->_authorization->isAllowed(self::PERMISSION_RESOURCE);
    }

}
?>
