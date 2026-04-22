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
 * @package    Bss_AddMultipleProducts
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Plugin\AddMultipleProducts\Helper;

use Bss\CompanyAccount\Model\Config\Source\Permissions;

class Data
{
    /**
     * @var \Bss\CompanyAccount\Helper\PermissionsChecker
     */
    private $permissionChecker;
    
    public function __construct(\Bss\CompanyAccount\Helper\PermissionsChecker $permissionChecker)
    {
        $this->permissionChecker = $permissionChecker;
    }
    
    public function afterIsEnableOtherPageQuoteExtension($subject, $result)
    {
        return $result && $this->permissionChecker->allowQuote(Permissions::ADD_TO_QUOTE);
    }
}
