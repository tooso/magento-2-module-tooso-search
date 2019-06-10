<?php declare(strict_types=1);

namespace Bitbull\Tooso\Api\Service\Search;

/**
 * @category Bitbull
 * @package  Bitbull_Tooso
 * @author   Fabio Gollinucci <fabio.gollinucci@bitbull.it>
 */
interface UrlRewriteSwitcherInterface
{
    /**
     * @param string $redirectUrl
     * @return string
     */
    public function elaborate($redirectUrl);
}
