<?php declare(strict_types=1);

namespace Bitbull\Tooso\Api\Service\Indexer;

interface OperationInterface
{
    /**
     * @var array $ids
     * @return void
     */
    public function execute($ids = null);
}
