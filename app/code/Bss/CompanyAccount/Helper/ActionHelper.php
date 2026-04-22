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

namespace Bss\CompanyAccount\Helper;

use Bss\CompanyAccount\Api\Data\SubRoleInterface as Role;
use Bss\CompanyAccount\Exception\CantDeleteAssignedRole;
use Bss\CompanyAccount\Exception\CompareException;
use Bss\CompanyAccount\Exception\EmptyInputException;
use Magento\Framework\Phrase;

/**
 * Class ActionHelper
 *
 * @package Bss\CompanyAccount\Helper
 */
class ActionHelper
{
    /**
     * @var CheckAssignedRole
     */
    private $checkAssignedRole;

    /**
     * @var Data
     */
    private $helper;

    /**
     * ActionHelper constructor.
     *
     * @param CheckAssignedRole $checkAssignedRole
     * @param Data $helper
     */
    public function __construct(
        CheckAssignedRole $checkAssignedRole,
        Data              $helper
    ) {
        $this->checkAssignedRole = $checkAssignedRole;
        $this->helper = $helper;
    }

    /**
     * Save role
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Bss\CompanyAccount\Api\Data\SubRoleInterfaceFactory $roleFactory
     * @param \Bss\CompanyAccount\Api\SubRoleRepositoryInterface $roleRepository
     * @param int $customerId
     *
     * @return Phrase|string
     * @throws EmptyInputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException|\Magento\Framework\Exception\LocalizedException
     */
    public function saveRole($request, $roleFactory, $roleRepository, $customerId)
    {
        $roleId = $request->getParam('role_id', '');
        /** @var \Bss\CompanyAccount\Api\Data\SubRoleInterface $role */
        $role = $roleFactory->create();
        $role->setRoleName($request->getParam(Role::NAME));
        $roleType = $request->getParam(Role::TYPE);
        if (empty($roleType) || !$roleType || $roleType === null) {
            $roleType = "";
        }
        if (is_array($roleType)) {
            $roleType = array_filter(
                $roleType,
                function ($value) {
                    return $value != "0";
                }
            );
            $role->setRoleType(implode(',', $roleType));
        } else {
            $role->setRoleType($roleType);
        }
        $maxAmount = trim($request->getParam(Role::MAX_ORDER_AMOUNT));
        $maxOrderPerDay = trim($request->getParam(Role::MAX_ORDER_PER_DAY));
        if ($maxAmount != "") {
            $maxAmount = $this->helper->convertCurrency($maxAmount, false);
        } else {
            $maxAmount = null;
        }
        $role->setMaxOrderPerDay($maxOrderPerDay == "" ? null : $maxOrderPerDay);
        $role->setMaxOrderAmount($maxAmount);
        $role->setCompanyAccount((int)$customerId);

        if (!empty($roleId)) {
            $role->setRoleId((int)$roleId);
            $message = __('Role has been updated.');
        } else {
            $role->setRoleId(null);
            $message = __('New role has been added.');
        }

        $roleRepository->save($role);

        return $message;
    }

    /**
     * Destroy role
     *
     * @param \Bss\CompanyAccount\Api\SubRoleRepositoryInterface $roleRepository
     * @param int $roleId
     * @param bool $isMassAction
     *
     * @return string
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws CantDeleteAssignedRole
     */
    public function destroyRole($roleRepository, $roleId, $isMassAction = false)
    {
        if ($this->checkAssignedRole->beAssigned($roleId)) {
            if ($isMassAction) {
                return false;
            }
            throw new CantDeleteAssignedRole(__('You can\'t delete this role, it was assigned to sub-user(s)'));
        }
        if ((int)$roleId !== 0) {
            $roleRepository->deleteById((int)$roleId);
            $message = __('You deleted the role.');
        } else {
            $message = __('We can\'t delete this default admin role for you right now.');
        }
        return $message;
    }

    /**
     * Reset password sub-user
     *
     * @return Phrase|string
     * @throws CompareException
     */
    public function resetPasswordSubUser($subUserHelper, $emailHelper, $customer, $subId)
    {
        /** @var \Bss\CompanyAccount\Api\Data\SubUserInterface $subUser */
        $subUser = $subUserHelper->getBy($subId);
        if ($subUser->getCustomerId() != (int)$customer->getId()) {
            throw new CompareException(__('Company Account not match.'));
        }
        $subUser = $subUserHelper->generateResetPasswordToken($subUser);
        $subUserHelper->save($subUser);
        return $emailHelper->sendResetPasswordMailToSubUser($customer, $subUser);
    }
}
