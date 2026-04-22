<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_B2bRegistration
 * @author     Extension Team
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\B2bRegistration\Model;

use Bss\B2bRegistration\Helper\Data;
use Bss\B2bRegistration\Model\Config\Source\CustomerAttribute;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerB2b
{
    /**
     * @var bool|null
     */
    protected $isB2bAccount = null;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Construct function
     *
     * @param Data $helper
     * @param Session $customerSession
     */
    public function __construct(
        Data    $helper,
        Session $customerSession
    ) {
        $this->customerSession = $customerSession;
        $this->helper = $helper;
    }

    /**
     * Check Customer is B2bAccount
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function isB2bAccount()
    {
        if ($this->isB2bAccount === null) {
            if ($this->helper->isEnable()) {
                $getCustomer = $this->customerSession->getCustomerData();
                if ($getCustomer) {
                    $customerB2bStatus = $getCustomer->getCustomAttribute('b2b_activasion_status');
                    if ($customerB2bStatus && $customerB2bStatus->getValue() == CustomerAttribute::B2B_APPROVAL) {
                        $this->isB2bAccount = true;
                    }
                }
            }
            if ($this->isB2bAccount === null) {
                $this->isB2bAccount = false;
            }
        }
        return $this->isB2bAccount;
    }
}
