<?php declare(strict_types=1);

namespace Bitbull\Tooso\Observer\Search;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\App\ActionFlag;
use Bitbull\Tooso\Api\Service\ConfigInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;
use Bitbull\Tooso\Api\Service\SearchInterface;
use Bitbull\Tooso\Api\Service\SessionInterface;
use Tooso\SDK\Search\Result;

class ExecuteToosoSearch implements ObserverInterface
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
     * @param LoggerInterface $logger
     * @param ConfigInterface $config
     * @param SearchInterface $search
     * @param ActionFlag $actionFlag
     */
    public function __construct(
        LoggerInterface $logger,
        ConfigInterface $config,
        SearchInterface $search,
        ActionFlag $actionFlag
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->search = $search;
        $this->actionFlag = $actionFlag;
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

        $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
        /** @var \Magento\CatalogSearch\Controller\Result\Index\Interceptor $controllerAction */
        $controllerAction = $observer->getControllerAction();
        $response = $controllerAction->getResponse();
        $response->setRedirect($redirect);

    }
}
