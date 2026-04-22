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
use Magento\Checkout\Controller\Index\Index;
use Magento\Checkout\Model\Cart;

/**
 * Class PlaceAnOrder
 *
 * @package Bss\CompanyAccount\Plugin\Customer\Permissions
 */
class PlaceOrder
{
    /**
     * @var PermissionsChecker
     */
    private $permissionsChecker;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * PlaceAnOrder constructor.
     *
     * @param PermissionsChecker $permissionsChecker
     * @param Cart $cart
     */
    public function __construct(
        PermissionsChecker $permissionsChecker,
        Cart               $cart
    ) {
        $this->permissionsChecker = $permissionsChecker;
        $this->cart = $cart;
    }

    /**
     * Check if logged in is sub-user and have permission to access this action
     *
     * @param Index $subject
     * @param callable $proceed
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundExecute(
        Index    $subject,
        callable $proceed
    ) {
        $cartAmount = $this->cart->getQuote()->getBaseSubtotal();
        $orderAmount = $this->permissionsChecker->isDenied(Permissions::MAX_ORDER_AMOUNT, $cartAmount);
        $orderCount = $this->permissionsChecker->isDenied(Permissions::MAX_ORDER_PERDAY);
        if($orderAmount || $orderCount){
            if (((count($orderAmount) == 2 || count($orderCount)) == 2)
                && ($orderAmount['is_denied'] || $orderCount['is_denied'])) {
                return $this->permissionsChecker->checkOrderPermission();
            }
        }
        return $proceed();

    }
}
