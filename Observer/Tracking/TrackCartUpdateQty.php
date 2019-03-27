<?php declare(strict_types=1);

namespace Bitbull\Tooso\Observer\Tracking;

use Bitbull\Tooso\Api\Service\ConfigInterface;
use Bitbull\Tooso\Api\Service\Config\AnalyticsConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;
use Bitbull\Tooso\Api\Service\TrackingInterface;

class TrackCartUpdateQty implements ObserverInterface
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
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var TrackingInterface
     */
    protected $tracking;

    /**
     * @param LoggerInterface $logger
     * @param TrackingInterface $tracking
     * @param ConfigInterface $config
     * @param AnalyticsConfigInterface $analyticsConfig
     */
    public function __construct(
        LoggerInterface $logger,
        TrackingInterface $tracking,
        ConfigInterface $config,
        AnalyticsConfigInterface $analyticsConfig
    ) {
        $this->logger = $logger;
        $this->tracking = $tracking;
        $this->config = $config;
        $this->analyticsConfig = $analyticsConfig;
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

        $items = $observer->getCart()->getQuote()->getItems();
        $info = $observer->getInfo()->getData();

        foreach ($items as $item) {
            $qtyFrom = $item->getQty();
            $qtyTo = $info[$item->getId()]['qty'];
            $qtyDiff = round($qtyTo) - round($qtyFrom);

            $this->logger->info('[cart tracking update] Product "'. $item->getName() .'" change qty from ' . $qtyFrom . ' to ' . $qtyTo);

            if ($qtyDiff > 0) {
                $event = 'add';
            }else if($qtyDiff < 0){
                $event = 'remove';
            }else{
                $this->logger->warn('[cart tracking update] Product "'. $item->getName() .'" has no changes, qty delta is 0, skipping..');
                continue;
            }

            $productData = $this->tracking->getProductTrackingParams($item, 1, abs($qtyDiff));

            $this->logger->info('[cart tracking update] Elaborated event '. $event .' for product "' . $productData['name'] . '" with qty ' . $productData['quantity']);

            $this->tracking->executeTrackingRequest([
                't' => 'event',
                'pr1id' => $productData['id'],
                'pr1nm' => $productData['name'],
                'pr1ca' => $productData['category'],
                'pr1br' => $productData['brand'],
                'pr1pr' => $productData['price'],
                'pr1qt' => $productData['quantity'],
                'pa' => $event,
                'ec' => 'cart',
                'ea' => $event,
            ]);
        }
    }
}
