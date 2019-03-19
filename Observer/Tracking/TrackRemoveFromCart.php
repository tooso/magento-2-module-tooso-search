<?php declare(strict_types=1);

namespace Bitbull\Tooso\Observer\Tracking;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Bitbull\Tooso\Api\Service\ConfigInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;
use Bitbull\Tooso\Api\Service\TrackingInterface;

class TrackRemoveFromCart implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var TrackingInterface
     */
    protected $tracking;

    /**
     * @param LoggerInterface $logger
     * @param ConfigInterface $config
     * @param TrackingInterface $tracking
     */
    public function __construct(
        LoggerInterface $logger,
        ConfigInterface $config,
        TrackingInterface $tracking
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->tracking = $tracking;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isTrackingEnabled() === false) {
            return;
        }

        /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
        $quoteItem = $observer->getQuoteItem();

        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $quoteItem->getProduct();
        $productData = $this->tracking->getProductTrackingParams($product, 1, round($quoteItem->getQty()));

        $this->logger->info('[cart tracking add] Product "'. $productData['name'] .'" with qty ' . $productData['quantity'] . ' removed from cart');

        $this->tracking->executeTrackingRequest([
            't' => 'event',
            'pr1id' => $productData['id'],
            'pr1nm' => $productData['name'],
            'pr1ca' => $productData['category'],
            'pr1br' => $productData['brand'],
            'pr1pr' => $productData['price'],
            'pr1qt' => $productData['quantity'],
            'pa' => 'remove',
            'ec' => 'cart',
            'ea' => 'remove',
        ]);
    }
}
