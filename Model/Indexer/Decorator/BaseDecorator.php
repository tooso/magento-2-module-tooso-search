<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Indexer\Decorator;

use Bitbull\Tooso\Model\Indexer\Operation;
use Bitbull\Tooso\Api\Service\LoggerInterface;

class BaseDecorator implements OperationInterface
{
    /**
     * @var OperationInterface
     */
    protected $operation;
    
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * @var OperationInterface $operation
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;
    }
    
    /**
     * @inheritdoc
     */
    public function getData()
    {
        return $this->operation->getData();
    }
    
}
