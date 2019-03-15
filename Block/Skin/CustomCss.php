<?php
namespace Bitbull\Tooso\Block\Skin;

use Bitbull\Tooso\Api\Block\ScriptInterface;
use Bitbull\Tooso\Api\Service\Config\SkinConfigInterface;
use Magento\Framework\View\Element\Template\Context;

class CustomCss extends \Magento\Framework\View\Element\Template implements ScriptInterface
{
    const SCRIPT_ID = 'tooso-skin-customcss';

    /**
     * @var SkinConfigInterface
     */
    protected $skinConfig;

    /**
     * Library constructor.
     *
     * @param Context $context
     * @param SkinConfigInterface $skinConfig
     */
    public function __construct(
        Context $context,
        SkinConfigInterface $skinConfig
    ) {
        parent::__construct($context);
        $this->skinConfig = $skinConfig;
    }

    /**
     * @inheritdoc
     */
    public function getScriptId()
    {
        return self::SCRIPT_ID;
    }

    /**
     * Get custom CSS
     *
     * @return string
     */
    public function getGetCustomCss()
    {
        return $this->skinConfig->getCustomCss();
    }
}
