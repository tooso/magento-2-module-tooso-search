<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Indexer\Decorator;

use Bitbull\Tooso\Model\Indexer\Operation;
use Bitbull\Tooso\Api\Service\LoggerInterface;

class VariantsDecorator implements OperationInterface
{
    /**
     * @inheritdoc
     */
    public function getData()
    {
        $originalData = parent::getData();
        return array_merge($originalData, [
            'variants' => json_encode([
                [
                    'id' => 'var-1',
                    'name' => 'Variants 1'
                ]
            ]),
        ]);
    }
}
