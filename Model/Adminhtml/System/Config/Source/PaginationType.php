<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Adminhtml\System\Config\Source;

class PaginationType implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return stores for backend select options
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'no-ajax', 'label' => 'No AJAX'],
            ['value' => 'ajax-pagination', 'label' => 'AJAX Pagination'],
            ['value' => 'ajax-infinite-scroll', 'label' => 'AJAX Infinite Scroll'],
        ];
    }
}
