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
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Block\Adminhtml\Edit\Role;

/**
 * Class CancelButton
 *
 * @package Bss\CompanyAccount\Block\Adminhtml\Edit\Role
 */
class CancelButton extends \Bss\CompanyAccount\Block\Adminhtml\Edit\Button\CancelButton
{
    /**
     * CancelButton constructor.
     */
    public function __construct()
    {
        $this->targetName = 'customer_form.areas.bss_company_account_manage_role.'
            . 'bss_company_account_manage_role.'
            . 'bss_companyaccount_customer_listroles_update_modal';
    }
}
