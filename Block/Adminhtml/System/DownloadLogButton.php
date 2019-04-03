<?php declare(strict_types=1);

namespace Bitbull\Tooso\Block\Adminhtml\System;

use Magento\Backend\Block\Template\Context;

class DownloadLogButton extends AbstractButton
{
    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('tooso/system/downloadLog');
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
                'label' => __('Download'),
            ]
        );

        return $button->toHtml();
    }

    /**
     * @inheritdoc
     */
    public function getButtonId()
    {
        return 'download_log_button';
    }
}
?>
