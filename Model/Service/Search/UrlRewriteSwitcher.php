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
        // Check query string
        $queryString = parse_url($redirectUrl, PHP_URL_QUERY);
        parse_str($queryString, $queryStringParts);
        if (
            $queryString !== null &&
            isset($queryStringParts[self::REDIRECT_AUTO_STORE_QUERY]) &&
            $queryStringParts[self::REDIRECT_AUTO_STORE_QUERY] === self::REDIRECT_AUTO_STORE_QUERY_VALUE
        ) {
            // Elaborate store code from path
            $urlPathParts = explode('/', ltrim(parse_url($redirectUrl, PHP_URL_PATH), '/'));
            $storeCodeRedirect = array_shift($urlPathParts);
            $urlPath = implode('/', $urlPathParts);
            try {
                $currentStore = $this->storeManager->getStore();
                $redirectStore = $this->storeRepository->get($storeCodeRedirect);

                // Get current rewrite
                $currentRewrite = $this->urlFinder->findOneByData([
                    UrlRewrite::REQUEST_PATH => $urlPath,
                    UrlRewrite::STORE_ID => $redirectStore->getId(),
                ]);
                if ($currentRewrite === null) {
                    $this->logger->debug("[url rewrite] Cannot find path '$urlPath' for store '".$redirectStore->getCode()."'");
                    return $redirectUrl;
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
                    return $redirectUrl;
                }

                // Override redirect url
                $redirectUrl = '/' . $currentStore->getCode() . '/' . $redirectRewrite->getRequestPath();

                // Preserve query parameters
                unset($queryStringParts[self::REDIRECT_AUTO_STORE_QUERY]);
                if (sizeof($queryStringParts) > 0) {
                    $redirectUrl .= '?'.http_build_query($queryStringParts);
                }

            } catch (NoSuchEntityException $exception) {
                $this->logger->error($exception->getMessage());
                $this->logger->debug("[url rewrite] Store '$storeCodeRedirect' found in path is not a valid store code");
            }
        } else {
            $this->logger->debug("[url rewrite] Not found query '".self::REDIRECT_AUTO_STORE_QUERY."' for redirect '$redirectUrl', do not performing redirect");
        }

        // Fallback
        return $redirectUrl;
    }
}
