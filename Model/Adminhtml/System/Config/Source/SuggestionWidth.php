<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Adminhtml\System\Config\Source;

class SuggestionWidth implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return stores for backend multiselect options
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => 'Auto (input field width)',
                'value' => 'auto'
            ],
            [
                'label' => 'Flex (max suggestion size)',
                'value' => 'flex'
            ],
            [
                'label' => 'Custom pixel value',
                'value' => 'custom'
            ]
        ];
    }
}
