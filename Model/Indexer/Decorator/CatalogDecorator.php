<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Indexer\Decorator;

use Bitbull\Tooso\Api\Service\LoggerInterface;

class CatalogDecorator implements BaseDecorator
{
    /**
     * @inheritdoc
     */
    public function getData()
    {
        $originalData = parent::getData();
        return array_merge($originalData, [
            'name' => 'Product name',
            'description' => 'This is the product description'
        ]);
    }
}
