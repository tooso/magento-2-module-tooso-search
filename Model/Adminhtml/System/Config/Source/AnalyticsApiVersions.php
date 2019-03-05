<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Adminhtml\System\Config\Source;

class AnalyticsApiVersions implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return stores for backend multiselect options
     */
    public function toOptionArray()
    {
        return [
            ['value' => '1', 'label' => 'v1'],
        ];
    }
}
