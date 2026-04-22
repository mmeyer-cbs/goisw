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
use Bss\CompanyAccount\Model\Config\Source\Permissions;

/**
 * Class MyQuote
 *
 * @package Bss\CompanyAccount\Plugin\Block\View
 */
class Submit
{
    /**
     * @var PermissionsChecker
     */
    protected $permissionsChecker;

    /**
     * MyQuote constructor.
     *
     * @param PermissionsChecker $permissionsChecker
     */
    public function __construct(
        PermissionsChecker $permissionsChecker
    ) {
        $this->permissionsChecker = $permissionsChecker;
    }

    /**
     * Check Submit quote with sub-user Company Account
     *
     * @param Object $subject
     * @param array $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetCheckoutConfig($subject, $result)
    {
        $allowAddToQuote = $this->permissionsChecker->allowQuote(Permissions::ADD_TO_QUOTE);
        if($allowAddToQuote) {
            $result["addToQuote"] = true;
        } else {
            $result["addToQuote"] = false;
        }
        return $result;
    }
}
