<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Adminhtml\System\Config\Source;

class SuggestionBuckets implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return stores for backend multiselect options
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => 'History',
                'value' => 'history'
            ],
            [
                'label' => 'Relevance',
                'value' => 'relevance'
            ],
            [
                'label' => 'Trending',
                'value' => 'trending'
            ]
        ];
    }
}
