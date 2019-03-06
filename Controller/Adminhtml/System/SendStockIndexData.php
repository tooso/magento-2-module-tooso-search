<?php
namespace Bitbull\Tooso\Controller\Adminhtml\System;

use Bitbull\Tooso\Api\Service\Indexer\DataSenderInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Bitbull\Tooso\Api\Service\LoggerInterface;

class SendStockIndexData extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DataSenderInterface
     */
    private $dataSender;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     * @param DataSenderInterface $dataSender
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        DataSenderInterface $dataSender
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->dataSender = $dataSender;
        parent::__construct($context);
    }

    /**
     * Collect relations data
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $this->logger->debug('[DataSender Stock] Request stock data send..');
        $this->dataSender->sendStock();
        $this->logger->debug('[DataSender Stock] Done!');

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();
        return $result->setData(['status' => 'ok']);
    }
}
?>
