<?php declare(strict_types=1);

namespace Bitbull\Tooso\Observer\Search;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ActionFlag;
use Bitbull\Tooso\Api\Service\ConfigInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;
use Bitbull\Tooso\Api\Service\SearchInterface;
use Bitbull\Tooso\Api\Service\SessionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreRepository;
use Magento\Store\Model\StoreSwitcher\CannotSwitchStoreException;
use Magento\Store\Model\StoreSwitcherInterface;
use Tooso\SDK\Search\Result;

class ExecuteToosoSearch implements ObserverInterface
{
    const REDIRECT_AUTO_STORE_QUERY = '___redirect=auto';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var SearchInterface
     */
    protected $search;

    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var ActionFlag
     */
    protected $actionFlag;

    /**
     * @var StoreSwitcherInterface
     */
    protected $storeSwitcher;

    /**
     * @var StoreRepository
     */
    protected $storeRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param LoggerInterface $logger
     * @param ConfigInterface $config
     * @param SearchInterface $search
     * @param ActionFlag $actionFlag
     * @param StoreSwitcherInterface $storeSwitcher
     * @param StoreRepository $storeRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        LoggerInterface $logger,
        ConfigInterface $config,
        SearchInterface $search,
        ActionFlag $actionFlag,
        StoreSwitcherInterface $storeSwitcher,
        StoreRepository $storeRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->search = $search;
        $this->actionFlag = $actionFlag;
        $this->storeSwitcher = $storeSwitcher;
        $this->storeRepository = $storeRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * Execute Tooso search
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isSearchEnabled() !== true) {
            $this->logger->debug('[catalog search result observer] Tooso search is disable, skip logic');
            return;
        }

        $this->logger->debug('[catalog search result observer] Executing search..');

        // Do search
        /** @var Result $result */
        $result = $this->search->execute();

        if (!$result->isValid()) {
            $this->logger->debug('[catalog search result observer] Search is not valid, skip logic');
            return;
        }

        // Check for redirect
        $redirect = $result->getRedirect();
        if (!$redirect) {
            $this->logger->debug('[catalog search result observer] Search has no redirect, skip logic');
            return;
        }

        $this->logger->debug("[catalog search result observer] Performing redirect to '$redirect'");

        // Check auto store redirect
        $queryString = parse_url($redirect, PHP_URL_QUERY);
        if ($queryString !== null && strpos($queryString, self::REDIRECT_AUTO_STORE_QUERY ) !== false) {
            $urlPaths = explode('/', ltrim(parse_url($redirect, PHP_URL_PATH), '/'));
            $storeCodeRedirect = array_shift($urlPaths);
            $fromStore = $this->storeManager->getStore();
            try {
                $targetStore = $this->storeRepository->get($storeCodeRedirect);
                $redirect = $this->storeSwitcher->switch($fromStore, $targetStore, $redirect);
            } catch (NoSuchEntityException $exception) {
                $this->logger->debug("[catalog search result observer] Store '$storeCodeRedirect' found in path is not a valid store code");
            } catch (CannotSwitchStoreException $exception) {
                $this->logger->debug("[catalog search result observer] Cannot switch from store '".$fromStore->getCode()."' to store '".$storeCodeRedirect."''");
            }
        }

        $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
        /** @var \Magento\CatalogSearch\Controller\Result\Index\Interceptor $controllerAction */
        $controllerAction = $observer->getControllerAction();
        $response = $controllerAction->getResponse();
        $response->setRedirect($redirect);
    }
}
