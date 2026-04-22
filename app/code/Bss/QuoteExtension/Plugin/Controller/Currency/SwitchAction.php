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

namespace Bss\QuoteExtension\Plugin\Controller\Currency;

use Bss\QuoteExtension\Model\QuoteExtension;
use Bss\QuoteExtension\Model\Session;

/**
 * Class SwitchAction
 * @package Bss\QuoteExtension\Plugin\Controller\Currency
 */
class SwitchAction
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
    public function afterExecute(
        \Magento\Directory\Controller\Currency\SwitchAction $subject,
        $result
    ) {
        $currency = $subject->getRequest()->getParam('currency');
        $quote = $this->checkoutSession->getQuoteExtension();
        $quote->setQuoteCurrencyCode(
            $currency
        );
        $this->quoteExtension->save($quote);
        return $result;
    }
}