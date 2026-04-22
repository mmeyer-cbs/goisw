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
namespace Bss\StoreCredit\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreFactory;
use Bss\StoreCredit\Model\ResourceModel\Credit as ResourceModelCredit;
use Bss\StoreCredit\Api\Data\StoreCreditInterface;

/**
 * Class Credit
 */
class Credit extends AbstractModel implements StoreCreditInterface
{
    /**
     * @var Currency
     */
    protected $currency;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    private $storeFactory;

    /**
     * @param Currency $currency
     * @param Context $context
     * @param Registry $registry
     * @param StoreFactory $storeFactory
     */
    public function __construct(
        \Bss\StoreCredit\Model\Currency $currency,
        Context $context,
        Registry $registry,
        StoreFactory $storeFactory
    ) {
        $this->currency = $currency;
        parent::__construct(
            $context,
            $registry
        );
        $this->storeFactory = $storeFactory;
    }

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(ResourceModelCredit::class);
    }

    /**
     * {@inheritdoc}
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * {@inheritdoc}
     */
    public function setBalanceAmount($amount)
    {
        return $this->setData(self::BALANCE_AMOUNT, $amount);
    }

    /**
     * @param int $websiteId
     * @return StoreCreditInterface|Credit
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     * @return float|mixed
     */
    public function getBalanceAmount()
    {
        return $this->getData(self::BALANCE_AMOUNT);
    }

    /**
     * @return int|mixed
     */
    public function getWebsiteId()
    {
        return $this->getData(self::WEBSITE_ID);
    }

    /**
     * @return array|int|mixed|null
     */
    public function getCustomId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     * @return array|int|mixed|null
     */
    public function getBanlaceId()
    {
        return $this->getData(self::BALANCE_ID);
    }

    /**
     * @inheridoc
     */
    public function setCurrencyCode($currencyCode)
    {
        return $this->setData(self::CURRENCY_CODE, $currencyCode);
    }

    /**
     * @inheridoc
     */
    public function getCurrencyCode()
    {
        return $this->getData(self::CURRENCY_CODE);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return float
     */
    public function getCreditInvoice($order)
    {
        $invoiceBaseBssStorecreditAmount = 0;
        if ($order->hasInvoices()) {
            foreach ($order->getInvoiceCollection() as $invoice) {
                $invoiceBaseBssStorecreditAmount += $invoice->getBaseBssStorecreditAmount();
            }
        }
        return $invoiceBaseBssStorecreditAmount;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return float
     */
    public function getCreditCreditmemo($order)
    {
        $creditmemoBaseBssStorecreditAmount = 0;
        if ($order->hasCreditmemos()) {
            foreach ($order->getCreditmemosCollection() as $creditmemo) {
                $creditmemoBaseBssStorecreditAmount += $creditmemo->getBaseBssStorecreditAmount();
            }
        }
        return $creditmemoBaseBssStorecreditAmount;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return float
     */
    public function getCreditRefundBase($order)
    {
        $creditmemoBaseBssStorecreditAmountRefund = 0;
        if (!$order) {
            return $creditmemoBaseBssStorecreditAmountRefund;
        }
        if ($order->hasCreditmemos()) {
            foreach ($order->getCreditmemosCollection() as $creditmemo) {
                $creditmemoBaseBssStorecreditAmountRefund += $creditmemo->getBaseBssStorecreditAmountRefund();
            }
        }
        return $creditmemoBaseBssStorecreditAmountRefund;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return float
     */
    public function getCreditRefund($order)
    {
        $creditmemoBssStorecreditAmountRefund = 0;
        if ($order->hasCreditmemos()) {
            foreach ($order->getCreditmemosCollection() as $creditmemo) {
                $creditmemoBssStorecreditAmountRefund += $creditmemo->getBssStorecreditAmountRefund();
            }
        }
        return $creditmemoBssStorecreditAmountRefund;
    }

    /**
     * Load store credit by customer
     *
     * @param int $customerId
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByCustomer($customerId)
    {
        return $this->_getResource()->loadByCustomer($this, $customerId);
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param float|null $baseBalance
     * @return bool
     */
    public function validateBalance($quote, $baseBalance = null)
    {
        $store = $this->storeFactory->create()->load($quote->getStoreId());
        $this->setWebsiteId($store->getWebsiteId());
        $credit = $this->loadByCustomer($quote->getCustomerId());
        if (!$credit->getId() || ($baseBalance == 0 && !$quote->getBaseBssStorecreditAmount())) {
            return false;
        }
        $baseBalance = $this->currency->convertCurrency($baseBalance, $quote->getBaseCurrencyCode(), $credit->getCurrency());
        if ($credit->getBalanceAmount() < $baseBalance) {
            return false;
        }
        return true;
    }
}
