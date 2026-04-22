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
namespace Bss\CompanyAccount\Plugin\Customer\Permissions;

use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;

/**
 * Class WishList
 *
 * @package Bss\CompanyAccount\Plugin\Customer\Permissions
 */
class WishList
{
    /**
     * @var PermissionsChecker
     */
    private $permissionsChecker;

    /**
     * WishList constructor.
     *
     * @param PermissionsChecker $permissionsChecker
     */
    public function __construct(PermissionsChecker $permissionsChecker)
    {
        $this->permissionsChecker = $permissionsChecker;
    }

    /**
     * Check if logged in is sub-user and have permission to access this action
     *
     * @param \Magento\Wishlist\Controller\Index\Index $subject
     * @param callable $proceed
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(\Magento\Wishlist\Controller\Index\Index $subject, callable $proceed)
    {
        $checkValue = $this->permissionsChecker->check(Permissions::ADD_VIEW_ACCOUNT_WISHLIST);
        if ($checkValue) {
            return $checkValue;
        }
        return $proceed();
    }
}
