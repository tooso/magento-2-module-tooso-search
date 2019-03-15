<?php declare(strict_types=1);
namespace Bitbull\Tooso\Model\Adminhtml\System\Config\Source;

use Magento\Store\Model\System\Store as SystemStore;

class Stores implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var SystemStore
     */
    protected $systemStore;

    /**
     * @param SystemStore $systemStore
     */
    public function __construct(
        SystemStore $systemStore
    ) {
        $this->systemStore = $systemStore;
    }
    
    /**
     * Return stores for backend multiselect options
     */
    public function toOptionArray()
    {
        return $this->systemStore->getStoreValuesForForm(false, true);
    }
}
