<?php
namespace Bitbull\Tooso\Controller\Adminhtml\System;

use Bitbull\Tooso\Api\Service\Indexer\AttributesValuesInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Bitbull\Tooso\Api\Service\LoggerInterface;

class AttributesValuesReindex extends Action
{
    const PERMISSION_RESOURCE = 'Bitbull_Tooso::attributes_values_reindex';

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AttributesValuesInterface
     */
    private $attributesValues;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     * @param AttributesValuesInterface $attributesValues
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        AttributesValuesInterface $attributesValues
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->attributesValues = $attributesValues;
        parent::__construct($context);
    }

    /**
     * Collect relations data
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $this->logger->debug('[Reindex Attributes Values] Request attributes reindex..');
        $this->attributesValues->execute([
            143
        ]);
        $this->logger->debug('[Reindex Attributes Values] Done!');

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();
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
