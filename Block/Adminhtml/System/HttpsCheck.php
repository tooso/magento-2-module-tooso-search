<?php declare(strict_types=1);

namespace Bitbull\Tooso\Block\Adminhtml\System;

use Magento\Store\Model\StoreManagerInterface;

class HttpsCheck extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        StoreManagerInterface $storeManager,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();

        ob_start();
        ?>
        <tr id="row_<?= $element->getHtmlId() ?>_php">
            <td class="label">
                <?= $element->getLabel() ?>
            </td>
            <?php if (strpos($baseUrl, 'https://') !== 0): ?>
                <td style="color: #e22626;" class="value">WARNING: Your website not use HTTPS for all frontend pages, this feature will not works properly</td>
            <?php else: ?>
                <td style="color: #185b00;" class="value">OK: Your website use HTTPS for frontend pages</td>
            <?php endif ?>
            <td></td>
        </tr>
        <?php
        $html = ob_get_clean();
        return $this->_decorateRowHtml($element, $html);
    }
}
