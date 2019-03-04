<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Service\Indexer\Enricher;

use Bitbull\Tooso\Api\Service\Indexer\EnricherInterface;
use Magento\CatalogInventory\Api\StockStateInterface;

class StockEnricher implements EnricherInterface
{
    /**
     * @var StockStateInterface
     */
    protected $stockState;

    /**
     * @var StockStateInterface $indexerConfig
     */
    public function __construct(StockStateInterface $stockState)
    {
        $this->stockState = $stockState;
    }

    /**
     * @inheritdoc
     */
    public function execute($data)
    {
        //TODO: load first data from StockStatusCollectionInterfaceFactory collection

        array_walk($data, function(&$elem) {
            $elem['is_in_stock'] = $this->stockState->verifyStock($elem['id']);
            $elem['qty'] = $this->stockState->getStockQty($elem['id']);
        });

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getEnrichedKeys()
    {
        return ['is_in_stock', 'qty'];
    }
}
