<?php declare(strict_types=1);

namespace Bitbull\Tooso\Model\Service\Search;

use Bitbull\Tooso\Api\Service\ConfigInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;
use Bitbull\Tooso\Api\Service\Search\UrlRewriteSwitcherInterface;
use Bitbull\Tooso\Api\Service\SearchInterface;
use Bitbull\Tooso\Api\Service\SessionInterface;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreRepository;
use Magento\Store\Model\StoreSwitcherInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class UrlRewriteSwitcher implements UrlRewriteSwitcherInterface
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
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @param LoggerInterface $logger
     * @param ConfigInterface $config
     * @param SearchInterface $search
     * @param ActionFlag $actionFlag
     * @param StoreRepository $storeRepository
     * @param StoreManagerInterface $storeManager
     * @param UrlFinderInterface $urlFinder
     */
    public function __construct(
        LoggerInterface $logger,
        ConfigInterface $config,
        SearchInterface $search,
        ActionFlag $actionFlag,
        StoreRepository $storeRepository,
        StoreManagerInterface $storeManager,
        UrlFinderInterface $urlFinder
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->search = $search;
        $this->actionFlag = $actionFlag;
        $this->storeRepository = $storeRepository;
        $this->storeManager = $storeManager;
        $this->urlFinder = $urlFinder;
    }

    /**
     * @inheritDoc
     */
    public function elaborate($redirectUrl)
    {
        $queryString = parse_url($redirectUrl, PHP_URL_QUERY);
        if ($queryString !== null && strpos($queryString, self::REDIRECT_AUTO_STORE_QUERY ) !== false) {
            $urlPathParts = explode('/', ltrim(parse_url($redirectUrl, PHP_URL_PATH), '/'));
            $storeCodeRedirect = array_shift($urlPathParts);
            $urlPath = implode('/', $urlPathParts);
            try {
                $currentStore = $this->storeManager->getStore();
                $redirectStore = $this->storeRepository->get($storeCodeRedirect);

                $currentRewrite = $this->urlFinder->findOneByData([
                    UrlRewrite::REQUEST_PATH => $urlPath,
                    UrlRewrite::STORE_ID => $redirectStore->getId(),
                ]);
                if ($currentRewrite === null) {
                    $this->logger->debug("[url rewrite] Cannot find path '$urlPath' for store '".$redirectStore->getCode()."'");
                    return $redirectUrl;
                }
                $redirectRewrite = $this->urlFinder->findOneByData([
                    UrlRewrite::TARGET_PATH => $currentRewrite->getTargetPath(),
                    UrlRewrite::STORE_ID => $currentStore->getId(),
                ]);
                if ($redirectRewrite === null) {
                    $this->logger->debug("[url rewrite] Cannot find path '$urlPath' for store '".$currentStore->getCode()."'");
                    return $redirectUrl;
                }
                return '/' . $currentStore->getCode() . '/' . $redirectRewrite->getRequestPath();
            } catch (NoSuchEntityException $exception) {
                $this->logger->error($exception->getMessage());
                $this->logger->debug("[url rewrite] Store '$storeCodeRedirect' found in path is not a valid store code");
            }
        } else {
            $this->logger->debug("[url rewrite] Not found query '".self::REDIRECT_AUTO_STORE_QUERY."' for redirect '$redirectUrl', do not performing redirect");
        }
        return $redirectUrl;
    }
}
