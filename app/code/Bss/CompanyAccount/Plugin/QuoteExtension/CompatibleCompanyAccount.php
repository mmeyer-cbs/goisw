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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyAccount\Plugin\QuoteExtension;

use Bss\CompanyAccount\Model\SubRoleRepository;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;

class CompatibleCompanyAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected \Magento\Customer\Model\Session $customerSession;

    /**
     * @var \Bss\CompanyAccount\Model\SubRoleRepository
     */
    protected \Bss\CompanyAccount\Model\SubRoleRepository $subRoleRepository;

    /**
     * Construct
     *
     * @param Session $customerSession
     * @param SubRoleRepository $subRoleRepository
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Bss\CompanyAccount\Model\SubRoleRepository $subRoleRepository
    ) {
        $this->customerSession = $customerSession;
        $this->subRoleRepository = $subRoleRepository;
    }

    /**
     * Get sub user by session
     *
     * @return bool|mixed
     */
    public function getSubUser()
    {
        if ($this->customerSession->getData('sub_user')) {
            return $this->customerSession->getData('sub_user');
        } else {
            return false;
        }
    }

    /**
     * Check role quote in my quote front end
     *
     * @param \Bss\QuoteExtension\ViewModel\CompatibleCompanyAccount $subject
     * @param string|false $result
     * @param \Bss\QuoteExtension\Model\ManageQuote $manageQuote
     * @return string|false
     * @throws LocalizedException
     */
    public function afterRoleCompanyAccount($subject, $result, $manageQuote)
    {
        if ($this->getSubUser()) {
            $subUser = $this->getSubUser();
            $roleId =  $subUser->getRoleId();
            $roleTypes = explode(',', $this->subRoleRepository->getById($roleId)->getRoleType() ?? "");
            if (in_array("10", $roleTypes) && in_array("11", $roleTypes)
                || in_array("0", $roleTypes) || in_array("11", $roleTypes)
            ) {
                return "place_order";
            }
            if (in_array("9", $roleTypes) && in_array("12", $roleTypes)
                || in_array("10", $roleTypes) && in_array("12", $roleTypes)
            ) {
                return "waiting_order";
            }
            if (in_array('9', $roleTypes)) {
                return false;
            }
        }
        return $result;
    }
}
