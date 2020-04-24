<?php declare(strict_types=1);

namespace Bitbull\Tooso\Plugin\Model\ResourceModel\Fulltext\Collection\SearchResultApplier;

use Bitbull\Tooso\Api\Service\ConfigInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplier;
use Magento\Framework\App\RequestInterface;

class DisableApply
{
    /**
     * @var ConfigInterface
     */
    protected $config;
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param ConfigInterface $config
     * @param RequestInterface $request
     */
    public function __construct(
        ConfigInterface $config,
        RequestInterface $request
    ) {
        $this->config = $config;
        $this->request = $request;
    }

    public function aroundApply(SearchResultApplier $subject, \Closure $next)
    {
        if ($this->isToosoSearchDisabled() || $this->isNotSearchPage()) {
            $next();
        }
    }

    private function isToosoSearchDisabled()
    {
        return $this->config->isSearchEnabled() !== true;
    }

    private function isNotSearchPage()
    {
        return $this->request->getFullActionName() !== 'catalogsearch_result_index';
    }
}
