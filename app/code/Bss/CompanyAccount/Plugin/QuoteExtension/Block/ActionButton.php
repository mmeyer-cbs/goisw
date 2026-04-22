<?php
declare(strict_types = 1);

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
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace  Bss\CompanyAccount\Plugin\QuoteExtension\Block;

use Bss\CompanyAccount\Helper\PermissionsChecker;
use Magento\Framework\Registry;

/**
 * Class ActionButton
 *
 * @package Bss\CompanyAccount\Plugin\Block\View
 */
class ActionButton
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var PermissionsChecker
     */
    protected $permissionsChecker;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * ActionButton constructor.
     *
     * @param Registry $registry
     * @param PermissionsChecker $permissionsChecker
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Registry $registry,
        PermissionsChecker $permissionsChecker,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->registry = $registry;
        $this->permissionsChecker = $permissionsChecker;
        $this->customerSession = $customerSession;
    }

    /**
     * Check Re-Submit quote with sub-user Company Account
     *
     * @param Object $subject
     * @param array $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCanSubmitQuote($subject, $result)
    {
        return $this->checkDisplayButton($subject, $result);
    }

    /**
     * Check action quotes with sub-user Company Account
     *
     * @param Object $subject
     * @param string|array $result
     * @return mixed
     */
    public function afterCanShowButton($subject, $result)
    {
        return $this->checkDisplayButton($subject, $result);
    }
    /**
     * Check display action quote Company Account
     *
     * @param Object $subject
     * @param array $result
     * @return mixed
     */
    public function checkDisplayButton($subject, $result)
    {
        if ($this->checkRoleAdmin()) {
            return $result;
        }
        $subUserIdQuote = $this->registry->registry("sub_user_id_quote");
        $subUserIdCurrent = $subject->getRequest()->getParam("sub_user_id_current");
        if ($subUserIdCurrent && $subUserIdQuote != $subUserIdCurrent
            && $subject->getNameInLayout() != "quoteextension_print"
        ) {
            return false;
        }
        return $result;
    }

    /**
     * Check sub user role admin
     *
     * @return bool
     */
    public function checkRoleAdmin()
    {
        if ($this->customerSession->getData('sub_user')) {
            $subUser = $this->customerSession->getData('sub_user');
            $roleIds = explode(',', $subUser->getData('role_id') ?? "");
            if (in_array("0", $roleIds)) {
                return true;
            }
        }
        return false;
    }
}
