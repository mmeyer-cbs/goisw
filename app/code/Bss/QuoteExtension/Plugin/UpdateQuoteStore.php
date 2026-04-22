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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\QuoteExtension\Plugin;

use Bss\QuoteExtension\Model\Session;
use Bss\QuoteExtension\Model\QuoteExtension;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreCookieManagerInterface;

/**
 * Updates quote store id.
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class UpdateQuoteStore
{
    /**
     * @var QuoteExtension
     */
    private $quoteExtension;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * UpdateQuoteStore constructor.
     * @param QuoteExtension $quoteExtension
     * @param Session $checkoutSession
     */
    public function __construct(
        QuoteExtension $quoteExtension,
        Session $checkoutSession
    ) {
        $this->quoteExtension = $quoteExtension;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param StoreCookieManagerInterface $subject
     * @param $result
     * @param StoreInterface $store
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSetStoreCookie(
        StoreCookieManagerInterface $subject,
        $result,
        StoreInterface $store
    ) {
        $storeCodeFromCookie = $subject->getStoreCodeFromCookie();
        if (null === $storeCodeFromCookie) {
            return;
        }
        $quote = $this->checkoutSession->getQuoteExtension();
        if ($quote->getIsActive() && $store->getCode() != $storeCodeFromCookie) {
            $quote->setStoreId(
                $store->getId()
            );
            $this->quoteExtension->save($quote);
        }
    }
}
