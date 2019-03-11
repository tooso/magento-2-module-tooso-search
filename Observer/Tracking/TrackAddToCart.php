<?php declare(strict_types=1);

namespace Bitbull\Tooso\Observer\Tracking;

use Bitbull\Tooso\Api\Service\Config\AnalyticsConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;
use Bitbull\Tooso\Api\Service\TrackingInterface;

class TrackAddToCart implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var AnalyticsConfigInterface
     */
    protected $analyticsConfig;

    /**
     * @var TrackingInterface
     */
    protected $tracking;

    /**
     * @param LoggerInterface $logger
     * @param TrackingInterface $tracking
     * @param AnalyticsConfigInterface $analyticsConfig
     */
    public function __construct(
        LoggerInterface $logger,
        TrackingInterface $tracking,
        AnalyticsConfigInterface $analyticsConfig
    ) {
        $this->logger = $logger;
        $this->tracking = $tracking;
        $this->analyticsConfig = $analyticsConfig;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $observer->getProduct();
        $productData = $this->tracking->getProductTrackingParams($product);

        $this->tracking->executeTrackingRequest([
            't' => 'event',
            'pr1id' => $productData['id'],
            'pr1nm' => $productData['name'],
            'pr1ca' => $productData['category'],
            'pr1br' => $productData['brand'],
            'pr1pr' => $productData['price'],
            'pr1qt' => round($product->getCartQty()),
            'pa' => 'add',
            'ec' => 'cart',
            'ea' => 'add',
        ]);
    }
}
