<?php declare(strict_types=1);

namespace Bitbull\Tooso\Plugin\Model\ResourceModel\Fulltext\Collection\SearchResultApplier;

use Bitbull\Tooso\Api\Service\ConfigInterface;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\SearchResultApplier;

class DisableApply
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
    }

    public function aroundApply(SearchResultApplier $subject, \Closure $next)
    {
        if ($this->config->isSearchEnabled() !== true) {
            $next();
        }
    }
}
