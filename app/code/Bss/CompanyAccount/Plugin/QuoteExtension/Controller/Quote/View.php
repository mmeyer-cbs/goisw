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
namespace  Bss\CompanyAccount\Plugin\QuoteExtension\Controller\Quote;

use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class CanAddToQuote
 *
 * @package Bss\CompanyAccount\Plugin\QuoteExtension
 */
class View
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
     * @param \Bss\QuoteExtension\Controller\Quote\View $subject
     * @return mixed
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute($subject)
    {
        $subUserIdCurrent = $this->permissionsChecker->getSubId();
        $subject->getRequest()->setParam("sub_user_id_current", $subUserIdCurrent);
        $allowViewQuotes = $this->permissionsChecker->allowQuote(Permissions::VIEW_QUOTES);
        $subject->getRequest()->setParam("allow_view_all_quotes", $allowViewQuotes);
        return $subject;
    }
}
