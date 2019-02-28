<?php declare(strict_types=1);

namespace Bitbull\Tooso\Block\Adminhtml\System;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class SendStockIndexDataButton extends AbstractButton
{
    /**
     * @inheritdoc
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('tooso/system/sendStockIndexData');
    }

    /**
     * @inheritdoc
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => $this->getButtonId(),
                'label' => __('Run'),
            ]
        );

        return $button->toHtml();
    }

    /**
     * @inheritdoc
     */
    public function getButtonId()
    {
        return 'send_stock_index_data';
    }
}
?>
