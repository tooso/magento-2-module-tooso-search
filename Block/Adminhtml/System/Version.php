<?php declare(strict_types=1);

namespace Bitbull\Tooso\Block\Adminhtml\System;

use Bitbull\Tooso\Api\Service\TrackingInterface;

class Version extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var TrackingInterface
     */
    protected $tracking;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param TrackingInterface $tracking
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Template\Context $context, TrackingInterface $tracking, array $data = [])
    {
        parent::__construct($context, $data);
        $this->tracking = $tracking;
    }

    /**
     * @inheritdoc
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        ob_start();
        ?>
        <tr id="row_<?= $element->getHtmlId() ?>_php">
            <td class="label">PHP</td>
            <td class="value"><?= $this->tracking->getPHPVersion() ?></td>
            <td></td>
        </tr>
        <tr id="row_<?= $element->getHtmlId() ?>_magento">
            <td class="label">Magento</td>
            <td class="value"><?= $this->tracking->getMagentoVersion() ?></td>
            <td></td>
        </tr>
        <tr id="row_<?= $element->getHtmlId() ?>_module">
            <td class="label">Module</td>
            <td class="value"><?= $this->tracking->getModuleVersion() ?></td>
            <td></td>
        </tr>
        <?php
        $html = ob_get_clean();
        return $this->_decorateRowHtml($element, $html);
    }
}
