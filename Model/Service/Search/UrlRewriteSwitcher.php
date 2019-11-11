<?php declare(strict_types=1);

namespace Bitbull\Tooso\Model\Service\Search;

use Bitbull\Tooso\Api\Service\ConfigInterface;
use Bitbull\Tooso\Api\Service\LoggerInterface;
use Bitbull\Tooso\Api\Service\Search\UrlRewriteSwitcherInterface;
use Bitbull\Tooso\Api\Service\SearchInterface;
use Bitbull\Tooso\Api\Service\SessionInterface;
use Magento\CmsUrlRewrite\Model\CmsPageUrlRewriteGenerator;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreRepository;
use Magento\Store\Model\StoreSwitcherInterface;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class UrlRewriteSwitcher implements UrlRewriteSwitcherInterface
{
    const REDIRECT_AUTO_STORE_QUERY = '___redirect';
    const REDIRECT_AUTO_STORE_QUERY_VALUE = 'auto';

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
        // Fallback with the same URL
        $finalRedirect = $redirectUrl;

        // Check query string
        $queryString = parse_url($redirectUrl, PHP_URL_QUERY);
        if ($queryString !== null) {
            parse_str($queryString, $queryStringParts);
            if (
                isset($queryStringParts[self::REDIRECT_AUTO_STORE_QUERY]) &&
                $queryStringParts[self::REDIRECT_AUTO_STORE_QUERY] === self::REDIRECT_AUTO_STORE_QUERY_VALUE
            ) {
                // Get current store
                $currentStore = $this->storeManager->getStore();

                // Elaborate store code from path
                $path = parse_url($redirectUrl, PHP_URL_PATH);
                if ($path === null) {
                    $storeCodeRedirect = $currentStore->getCode();
                    $urlPath = '/';
                    $this->logger->warn("[url rewrite] Redirect '$redirectUrl' does not contain store code in path.");
                }else{
                    $urlPathParts = explode('/', ltrim($path, '/'));
                    $storeCodeRedirect = array_shift($urlPathParts);
                    $urlPath = implode('/', $urlPathParts);
                }
                try {
                    if ($storeCodeRedirect !== $currentStore->getCode()) {
                        $redirectStore = $this->storeRepository->get($storeCodeRedirect);
                    }else{
                        $redirectStore = $currentStore;
                    }

                    // Get current rewrite
                    $currentRewrite = $this->urlFinder->findOneByData([
                        UrlRewrite::REQUEST_PATH => $urlPath,
                        UrlRewrite::STORE_ID => $redirectStore->getId(),
                    ]);
                    if ($currentRewrite === null) {
                        $this->logger->debug("[url rewrite] Cannot find path '$urlPath' for store '".$redirectStore->getCode()."'");
                        return $finalRedirect;
                    }

                    // Get target rewrite
                    if ($currentRewrite->getEntityType() === CmsPageUrlRewriteGenerator::ENTITY_TYPE) {
                        $this->logger->debug("[url rewrite] Path '$urlPath' is a CMS, not possible to retrieve translated page, searching for the same path");
                        $redirectRewrite = $this->urlFinder->findOneByData([
                            UrlRewrite::REQUEST_PATH => $urlPath,
                            UrlRewrite::STORE_ID => $currentStore->getId(),
                        ]);
                    } else {
                        $redirectRewrite = $this->urlFinder->findOneByData([
                            UrlRewrite::TARGET_PATH => $currentRewrite->getTargetPath(),
                            UrlRewrite::STORE_ID => $currentStore->getId(),
                        ]);
                    }
                    if ($redirectRewrite === null) {
                        $this->logger->debug("[url rewrite] Cannot find path '$urlPath' for store '".$currentStore->getCode()."'");
                        return $finalRedirect;
                    }

                    // Override redirect url
                    $finalRedirect = '/' . $currentStore->getCode() . '/' . $redirectRewrite->getRequestPath();

                    // Preserve query parameters
                    unset($queryStringParts[self::REDIRECT_AUTO_STORE_QUERY]);
                    if (sizeof($queryStringParts) > 0) {
                        $finalRedirect .= '?'.http_build_query($queryStringParts);
                    }

                    // Preserve URL fragment
                    $urlFragment = parse_url($redirectUrl, PHP_URL_FRAGMENT);
                    if ($urlFragment !== null) {
                        $finalRedirect .=  '#'.$urlFragment;
                    }

                } catch (NoSuchEntityException $exception) {
                    $this->logger->error($exception->getMessage());
                    $this->logger->debug("[url rewrite] Store '$storeCodeRedirect' found in path is not a valid store code");
                }
            } else {
                $this->logger->debug("[url rewrite] Not found query '".self::REDIRECT_AUTO_STORE_QUERY."' for redirect '$redirectUrl', do not performing redirect manipulation");
            }
        } else {
            $this->logger->debug("[url rewrite] No query string found for redirect '$redirectUrl', do not performing redirect manipulation");
        }

        // Return redirect
        return $finalRedirect;
    }
}
