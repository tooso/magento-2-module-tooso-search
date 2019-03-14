<?php
namespace Bitbull\Tooso\Block\Search;

use Bitbull\Tooso\Api\Service\Config\SearchConfigInterface;
use Magento\Framework\View\Element\Template\Context;

class MessageStyle extends \Magento\Framework\View\Element\Template
{
    /**
     * @var SearchConfigInterface|null
     */
    public $searchConfig = null;

    /**
     * ClickAfterSearch constructor.
     *
     * @param Context $context
     * @param SearchConfigInterface $searchConfig
     */
    public function __construct(
        Context $context,
        SearchConfigInterface $searchConfig
    ) {
        parent::__construct($context);
        $this->searchConfig = $searchConfig;
    }

    /**
     * Get message style
     *
     * @return null|string
     */
    public function getMessageStyle()
    {
        return $this->searchConfig->getMessageStyle();
    }
}
