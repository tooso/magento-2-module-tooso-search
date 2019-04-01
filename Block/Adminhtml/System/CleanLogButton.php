<?php declare(strict_types=1);

namespace Bitbull\Tooso\Block\Adminhtml\System;

use Magento\Backend\Block\Template\Context;

class CleanLogButton extends AbstractButton
{
    /**
     * @inheritdoc
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('tooso/system/cleanLog');
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
                'label' => __('Clean'),
            ]
        );

        return $button->toHtml();
    }

    /**
     * @inheritdoc
     */
    public function getButtonId()
    {
        return 'clean_log_button';
    }
}
?>
