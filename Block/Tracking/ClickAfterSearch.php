<?php
namespace Bitbull\Tooso\Block\Tracking;

use Bitbull\Tooso\Api\Service\ConfigInterface;
use Bitbull\Tooso\Api\Service\SearchInterface;
use Magento\Framework\View\Element\Template\Context;

class ClickAfterSearch extends \Magento\Framework\View\Element\Template
{
    /**
     * @var SearchInterface
     */
    public $search;

    /**
     * @var ConfigInterface
     */
    public $config;

    /**
     * ClickAfterSearch constructor.
     *
     * @param Context $context
     * @param SearchInterface $search
     * @param ConfigInterface $config
     */
    public function __construct(
        Context $context,
        SearchInterface $search,
        ConfigInterface $config
    ) {
        parent::__construct($context);
        $this->search = $search;
        $this->config = $config;
    }
}
