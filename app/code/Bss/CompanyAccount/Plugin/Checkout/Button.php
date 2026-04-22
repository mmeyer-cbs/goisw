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

namespace Bss\CompanyAccount\Plugin\Checkout;

use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Magento\Checkout\Block\Onepage\Link;

/**
 * Class Button
 *
 * @package Bss\CompanyAccount\Plugin\Customer\Permissions
 */
class Button
{
    /**
     * @var PermissionsChecker
     */
    protected $permissionsChecker;

    /**
     * Button constructor.
     *
     * @param PermissionsChecker $permissionsChecker
     */
    public function __construct(
        PermissionsChecker $permissionsChecker
    ) {
        $this->permissionsChecker = $permissionsChecker;
    }

    /**
     * Function after is disabled
     *
     * @param Link $subject
     * @param $result
     * @return bool|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterIsDisabled(
        Link $subject,
             $result
    ) {
        if ($this->permissionsChecker->isDenied(Permissions::PLACE_ORDER_WAITING)) {
            if ($this->permissionsChecker->isDenied(Permissions::PLACE_ORDER)) {
                return true;
            }
        }
        return $result;
    }
}
