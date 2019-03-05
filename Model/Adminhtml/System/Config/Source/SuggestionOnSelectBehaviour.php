<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Adminhtml\System\Config\Source;

class SuggestionOnSelectBehaviour implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return stores for backend multiselect options
     */
    public function toOptionArray()
    {
        return [
            [
                'label' => 'Submit the form',
                'value' => 'submit'
            ],
            [
                'label' => 'Do nothing',
                'value' => 'nothing'
            ],
            [
                'label' => 'Custom',
                'value' => 'custom'
            ]
        ];
    }
}
