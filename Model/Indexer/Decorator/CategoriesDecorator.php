<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Indexer\Decorator;

use Bitbull\Tooso\Model\Indexer\Operation;
use Bitbull\Tooso\Api\Service\LoggerInterface;

class CategoriesDecorator implements OperationInterface
{
    /**
     * @inheritdoc
     */
    public function getData()
    {
        $originalData = parent::getData();
        return array_merge($originalData, [
            'categories' => 'Cat1/Cat2/Cat3',
        ]);
    }
}
