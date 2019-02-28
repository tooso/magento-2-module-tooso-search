<?php declare(strict_types=1);

namespace Bitbull\Tooso\Api\Service\Indexer;

interface DataSenderInterface
{
    /**
     * @return boolean
     */
    public function sendCatalog();

    /**
     * @return boolean
     */
    public function sendStock();
}
