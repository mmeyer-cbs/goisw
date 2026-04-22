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
 * @package    Bss_StoreCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\StoreCredit\Observer;

use Bss\StoreCredit\Helper\Data;
use Bss\StoreCredit\Model\Currency;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class OrderCreateApplyStoreCredit
 * @package Bss\StoreCredit\Observer
 */
class OrderCreateApplyStoreCredit implements ObserverInterface
{
    /**
     * @var \Bss\StoreCredit\Model\Currency
     */
    protected $currency;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var Data
     */
    private $bssStoreCreditHelper;

    /**
     * @param Currency $currency
     * @param ManagerInterface $messageManager
     * @param Data $bssStoreCreditHelper
     */
    public function __construct(
        \Bss\StoreCredit\Model\Currency  $currency,
        ManagerInterface $messageManager,
        Data $bssStoreCreditHelper
    ) {
        $this->currency = $currency;
        $this->messageManager = $messageManager;
        $this->bssStoreCreditHelper = $bssStoreCreditHelper;
    }

    /**
     * Save input store credit
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $data = $observer->getRequestModel()->getPost('order');
        $amount = '';
        if (isset($data) && isset($data['storecredit']['amount'])) {
            $amount = $data['storecredit']['amount'];
        }
        $quote = $observer->getOrderCreateModel()->getQuote();
        $shippingAmount = $quote->getShippingAddress()->getShippingAmount() ?? 0;
        $taxAmount = $quote->getTotals()['tax']->getValue() ?? 0;
        $message = $this->bssStoreCreditHelper->getSuccessApplyCreditMsg();
        $totals_amount = $quote->getData('subtotal_with_discount') + $shippingAmount + $taxAmount;
        if (!$this->bssStoreCreditHelper->getGeneralConfig('used_tax') &&
            !$this->bssStoreCreditHelper->getGeneralConfig('used_shipping')
        ) {
            $totals_amount = $quote->getData('subtotal_with_discount');
        } elseif (!$this->bssStoreCreditHelper->getGeneralConfig('used_tax')) {
            $totals_amount = $totals_amount - $taxAmount;
        } elseif (!$this->bssStoreCreditHelper->getGeneralConfig('used_shipping')) {
            $totals_amount = $totals_amount - $shippingAmount;
        }
        $baseAmount = $this->currency->convertCurrency($amount, $quote->getQuoteCurrencyCode(), $quote->getBaseCurrencyCode());
        if ($amount != '') {
            $amount = (float) $amount;
            if ($baseAmount >= 0) {
                if ($baseAmount > $totals_amount) {
                    $quote->setBssStorecreditAmountInput($totals_amount);
                    $quote->setBaseBssStorecreditAmountInput($totals_amount);
                } else {
                    $quote->setBssStorecreditAmountInput($amount);
                    $quote->setBaseBssStorecreditAmountInput($baseAmount);
                }
                $this->messageManager->addSuccessMessage($message);
            } else {
                $this->messageManager->addErrorMessage(__('Error'));
            }
        }
    }
}
