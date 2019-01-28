<?php declare(strict_types=1);

namespace Bitbull\Tooso\Block\Search;

use Magento\Framework\Message\AbstractMessage;

class SearchMessage extends AbstractMessage
{
    const MESSAGE_TYPE = 'tooso';

    /**
     * Getter message type
     *
     * @return string
     */
    public function getType()
    {
        return self::MESSAGE_TYPE;
    }
}
