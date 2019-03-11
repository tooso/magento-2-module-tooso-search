<?php declare(strict_types=1);

namespace Bitbull\Tooso\Api\Service\Config;

/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
interface SkinConfigInterface
{
    /**
     * Get custom CSS
     *
     * @return string
     */
    public function getCustomCss();

    /**
     * Check is custom CSS is enabled
     *
     * @return boolean
     */
    public function isCustomCssEnabled();
}
