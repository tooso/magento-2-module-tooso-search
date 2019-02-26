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
        array_walk($data, function(&$d) {
            $d['is_in_stock'] = $this->stockState->verifyStock($d['id']);
            $d['qty'] = $this->stockState->getStockQty($d['id']);
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
