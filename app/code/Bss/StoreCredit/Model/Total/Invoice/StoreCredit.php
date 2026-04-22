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
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\StoreCredit\Model\Total\Invoice;

use Bss\StoreCredit\Helper\Data;
use Magento\Quote\Model\QuoteFactory;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

/**
 * Class StoreCredit
 * @package Bss\StoreCredit\Model\Total\Invoice
 */
class StoreCredit extends AbstractTotal
{
    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \Bss\StoreCredit\Helper\Data
     */
    private $bssStoreCreditHelper;

    /**
     * StoreCredit construct
     *
     * @param QuoteFactory $quoteFactory
     * @param \Magento\Framework\App\State $state
     * @param Data $bssStoreCreditHelper
     * @param array $data
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        \Magento\Framework\App\State $state,
        Data $bssStoreCreditHelper,
        array $data = []
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->state = $state;
        parent::__construct($data);
        $this->bssStoreCreditHelper = $bssStoreCreditHelper;
    }

    /**
     * Apply store credit into invoice
     *
     * @param Invoice $invoice
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function collect(
        Invoice $invoice
    ) {
        parent::collect($invoice);
        $dataStoreCredit = $this->dataStoreCredit($invoice);
        $order = $invoice->getOrder();
        $baseBalance = $dataStoreCredit[0];
        $balance = $dataStoreCredit[1];

        if ($baseBalance && $balance) {
            if (!$invoice->getId() && !empty($order->getInvoiceCollection()->getData())) {
                $invoiceBaseBssStorecreditAmount = 0;
                $invoiceBssStorecreditAmount = 0;
                foreach ($order->getInvoiceCollection() as $invoiceOrder) {
                    $invoiceBaseBssStorecreditAmount += $invoiceOrder->getBaseBssStorecreditAmount();
                    $invoiceBssStorecreditAmount += $invoiceOrder->getBssStorecreditAmount();
                }
                $baseBalance -= $invoiceBssStorecreditAmount;
                $balance -= $invoiceBaseBssStorecreditAmount;
            }
            $baseGrandTotal = $invoice->getBaseGrandTotal();
            $grandTotal = $invoice->getGrandTotal();
            if ($baseBalance >= $baseGrandTotal) {
                $baseBalanceUsedLeft = $baseGrandTotal;
                $balanceUsedLeft = $grandTotal;
                $invoice->setBaseGrandTotal(0);
                $invoice->setGrandTotal(0);
            } else {
                $baseBalanceUsedLeft = $baseBalance;
                $balanceUsedLeft = $balance;
                $invoice->setBaseGrandTotal($baseGrandTotal - $baseBalanceUsedLeft);
                $invoice->setGrandTotal($grandTotal - $balanceUsedLeft);
            }
            $invoice->setBssStorecreditAmount($balanceUsedLeft);
            $invoice->setBaseBssStorecreditAmount($baseBalanceUsedLeft);
        }
    }

    /**
     * Data store credit
     *
     * @param Invoice $invoice
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function dataStoreCredit($invoice)
    {
        $order = $invoice->getOrder();
        $baseBalance = $order->getBaseBssStorecreditAmount();
        $balance = $order->getBssStorecreditAmount();
        if (!$order->getId()) {
            $quote = $this->quoteFactory->create()->loadByIdWithoutStore($order->getQuoteId());
            $baseBalance = $quote->getBaseBssStorecreditAmount();
            $balance = $quote->getBssStorecreditAmount();
            if ($baseBalance && $balance) {
                return [$baseBalance, $balance];
            }
            return [0, 0];
        }
        return [$baseBalance, $balance];
    }
}
