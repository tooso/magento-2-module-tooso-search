<?php
namespace Bitbull\Tooso\Block\Tracking;

use Bitbull\Tooso\Api\Block\ScriptInterface;

class PageView extends \Magento\Framework\View\Element\Template implements ScriptInterface
{
    const SCRIPT_ID = 'tooso-tracking-pageview';

    /**
     * @inheritdoc
     */
    public function getScriptId()
    {
        return self::SCRIPT_ID;
    }
}
