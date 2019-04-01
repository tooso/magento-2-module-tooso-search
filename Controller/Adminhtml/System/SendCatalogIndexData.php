<?php
namespace Bitbull\Tooso\Controller\Adminhtml\System;

use Bitbull\Tooso\Api\Service\Indexer\DataSenderInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Bitbull\Tooso\Api\Service\LoggerInterface;

class SendCatalogIndexData extends Action
{
    const PERMISSION_RESOURCE = 'Bitbull_Tooso::send_catalog_data';

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
        $this->logger->debug('[DataSender Catalog] Request catalog data send..');
        $isSuccess = $this->dataSender->sendCatalog();

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();

        if ($isSuccess === false) {
            return $result->setHttpResponseCode(500);
        }

        $this->logger->debug('[DataSender Catalog] Done!');
        return $result->setData(['status' => 'ok']);
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
