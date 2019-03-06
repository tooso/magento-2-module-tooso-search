<?php
namespace Bitbull\Tooso\Block\Tracking;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Bitbull\Tooso\Api\Service\Config\AnalyticsConfigInterface;
use Bitbull\Tooso\Api\Service\TrackingInterface;
use Bitbull\Tooso\Api\Block\ScriptInterface;

class Checkout extends \Magento\Framework\View\Element\Template implements ScriptInterface
{
    const SCRIPT_ID = 'tooso-tracking-checkout';

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var AnalyticsConfigInterface
     */
    protected $analyticsConfig;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var TrackingInterface
     */
    protected $tracking;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterface[]
     */
    protected $orderedProducts = [];

    /**
     * ProductView constructor.
     *
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param AnalyticsConfigInterface $analyticsConfig
     * @param OrderRepositoryInterface $orderRepository
     * @param TrackingInterface $tracking
     * @param ProductCollectionFactory $productCollectionFactory
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        AnalyticsConfigInterface $analyticsConfig,
        OrderRepositoryInterface $orderRepository,
        TrackingInterface $tracking,
        ProductCollectionFactory $productCollectionFactory
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->analyticsConfig = $analyticsConfig;
        $this->orderRepository = $orderRepository;
        $this->tracking = $tracking;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function getScriptId()
    {
        return self::SCRIPT_ID;
    }

    /**
     * Get order tracking parameters
     *
     * @return array|null
     */
    public function getOrderTrackingParams()
    {
        $order = $this->getOrder();
        if ($order === null) {
            return null;
        }
        return $this->tracking->getOrderTrackingParams($order);
    }

    /**
     * Get checkout order products tracking parameters
     *
     * @return array
     */
    public function getProductsTrackingParams()
    {
        $order = $this->getOrder();
        if ($order === null) {
            return [];
        }
        $this->loadOrderedProducts();
        $items = $order->getItems();
        return array_map(function($item, $index) {
            return $this->tracking->getProductTrackingParams(
                $this->orderedProducts[$item->getProductId()],
                $index,
                round($item->getQtyOrdered())
            );
        }, $items, array_keys($items));
    }

    /**
     * Get order
     *
     * @return \Magento\Sales\Api\Data\OrderInterface|null
     */
    public function getOrder()
    {
        $orderId = $this->checkoutSession->getLastOrderId();
        if ($orderId === null) {
            return null;
        }
        return $this->orderRepository->get($orderId);
    }

    /**
     * Check if Javascript library is included
     *
     * @return boolean
     */
    public function isLibraryIncluded()
    {
        return $this->analyticsConfig->isLibraryIncluded();
    }

    /**
     * Load order's products
     */
    private function loadOrderedProducts()
    {
        $order = $this->getOrder();
        if ($order === null) {
            return;
        }

        $productIds = array_map(function($item) {
            return $item->getProductId();
        }, $order->getItems());

        $productCollection = $this->productCollectionFactory->create()
            ->addAttributeToFilter('entity_id', $productIds)
            ->addAttributeToSelect('*');
        foreach ($productCollection as $product) {
            $this->orderedProducts[$product->getId()] = $product;
        }
    }
}
