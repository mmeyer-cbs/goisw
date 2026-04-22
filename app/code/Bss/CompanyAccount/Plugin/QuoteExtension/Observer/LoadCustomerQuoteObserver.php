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
namespace  Bss\CompanyAccount\Plugin\QuoteExtension\Observer;

use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class CanAddToQuote
 *
 * @package Bss\CompanyAccount\Plugin\QuoteExtension
 */
class LoadCustomerQuoteObserver
{
    /**
     * @var PermissionsChecker
     */
    protected $permissionsChecker;

    /**
     * CanAddToQuote constructor.
     * @param PermissionsChecker $permissionsChecker
     */
    public function __construct(
        PermissionsChecker $permissionsChecker
    ) {
        $this->permissionsChecker = $permissionsChecker;
    }

    /**
     * Check active request4quote with sub-user Company Account
     *
     * @param Object $subject
     * @param callable $proceed
     * @param Observer $observer
     * @return mixed
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute($subject, callable $proceed, Observer $observer)
    {
        if($this->permissionsChecker->allowQuote(Permissions::ADD_TO_QUOTE)) {
            return $proceed($observer);
        }
        return false;
    }
}
