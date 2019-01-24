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
    const QUERY_SEARCH_PARAM = 'q';

    /**
     * @var LoggerInterface|null
     */
    protected $logger = null;

    /**
     * @var ConfigInterface|null
     */
    protected $config = null;

    /**
     * @var SearchInterface|null
     */
    protected $search = null;

    /**
     * @var SessionInterface|null
     */
    protected $session = null;

    /**
     * @var ActionFlag|null
     */
    protected $actionFlag = null;

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

        $event = $observer->getEvent();
        /** @var RequestHttp $request */
        $request = $event->getRequest();
        $queryText = $request->getParam(self::QUERY_SEARCH_PARAM);

        // Do search
        /** @var Result $result */
        $result = $this->search->execute($queryText);

        if ($result->isValid()) {
            // Check for redirect
            $redirect = $result->getRedirect();
            if ($redirect !== null) {
                $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                /** @var \Magento\CatalogSearch\Controller\Result\Index\Interceptor $controllerAction */
                $controllerAction = $observer->getControllerAction();
                $response = $controllerAction->getResponse();
                $response->setRedirect($redirect);
                return;
            }
        }
    }
}
