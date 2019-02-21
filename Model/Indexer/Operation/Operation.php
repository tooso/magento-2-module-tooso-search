<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Indexer\Operation;

class Operation implements OperationInterface
{
    /**
     * @var array $data
     */
    protected $data = [];
    
    /**
     * @inheritdoc
     */
    public function getData()
    {
        return [
            'id' => 123
        ];
    }
}