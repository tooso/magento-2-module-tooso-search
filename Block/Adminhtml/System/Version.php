<?php declare(strict_types=1);

namespace Bitbull\Tooso\Block\Adminhtml\System;

class Version extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @inheritdoc
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $version = 'undefined';
        try {
            $reflection = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
            $vendorDir = dirname(dirname($reflection->getFileName()));
            $packages = json_decode(file_get_contents($vendorDir . '/composer/installed.json'), true);
            foreach ($packages as $package) {
                if ($package['name'] === 'bitbull/tooso') {
                    $version = $package['version'];
                    break;
                }
            }
        } catch (\Exception $e) {
            $version = 'error: ' . $e->getMessage();
        }

        ob_start();
        ?>
        <td class="label">Module Version</td>
        <td class="value"><?= $version ?></td>
        <td></td>
        <?php
        $html = ob_get_clean();
        return $this->_decorateRowHtml($element, $html);
    }
}
