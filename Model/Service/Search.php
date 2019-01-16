<?php

namespace Bitbull\Tooso\Service;

use Bitbull\Tooso\Api\Service\ConfigInterface;
use Bitbull\Tooso\Api\Service\SearchInterface;

class Search implements SearchInterface
{
    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * Search constructor.
     *
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }
}