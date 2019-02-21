<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Indexer\Operation;

interface OperationInterface
{
    /**
     * @return array
     */
    public function getData();
    
    /**
     * @var OperationInterface $operation
     */
    public function setOperation($operation);
}