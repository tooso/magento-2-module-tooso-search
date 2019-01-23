<?php
namespace Bitbull\Tooso\Block\Tracking;

use Bitbull\Tooso\Api\Service\SearchInterface;
use Magento\Framework\View\Element\Template\Context;

class ClickAfterSearch extends \Magento\Framework\View\Element\Template
{
    /**
     * @var SearchInterface|null
     */
    public $search = null;

    /**
     * ClickAfterSearch constructor.
     *
     * @param Context $context
     * @param SearchInterface $search
     */
    public function __construct(
        Context $context,
        SearchInterface $search
    )
    {
        parent::__construct($context);
        $this->search = $search;
    }
}
