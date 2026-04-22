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

namespace Bss\CompanyAccount\Plugin\Checkout\MultiAddresses;

use Bss\CompanyAccount\Helper\Data as Helper;
use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Magento\Multishipping\Helper\Data;

/**
 * Class Link
 *
 * @package Bss\CompanyAccount\Plugin\Customer\Permissions
 */
class Link
{
    /**
     * @var PermissionsChecker
     */
    protected $permissionsChecker;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * Link constructor.
     *
     * @param PermissionsChecker $permissionsChecker
     * @param Helper $helper
     */
    public function __construct(
        PermissionsChecker $permissionsChecker,
        Helper             $helper
    ) {
        $this->permissionsChecker = $permissionsChecker;
        $this->helper = $helper;
    }

    /**
     * Can sub-user checkout with multi address
     *
     * @param Data $subject
     * @param boolean $result
     * @return bool|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterIsMultishippingCheckoutAvailable(
        Data $subject,
             $result
    ) {
        if ($this->helper->isEnable() && $result == true) {
            if ($this->permissionsChecker->isDenied(Permissions::PLACE_ORDER)) {
                return false;
            } else {
                return true;
            }
        }
        return $result;
    }
}
