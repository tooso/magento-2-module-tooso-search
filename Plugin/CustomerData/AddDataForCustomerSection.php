<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Bitbull\Tooso\Plugin\CustomerData;

use \Magento\Framework\DataObject;
use Bitbull\Tooso\Api\Service\SessionInterface;

class AddDataForCustomerSection
{
    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * AddDataForCustomerSection constructor.
     *
     * @param SessionInterface $session
     */
    public function __construct(
        SessionInterface $session
    ) {
        $this->session = $session;
    }

    /**
     * Add data to customer
     *
     * @param \Magento\Framework\DataObject $subject
     * @param array $result
     * @return array
     */
    public function afterGetSectionData(DataObject $subject, $result)
    {
        $result['customerId'] = $this->getCustomerId();

        return $result;
    }

    /**
     * Get Magento customer ID
     *
     * @return integer
     */
    public function getCustomerId()
    {
        return $this->session->getCustomerId();
    }
}
