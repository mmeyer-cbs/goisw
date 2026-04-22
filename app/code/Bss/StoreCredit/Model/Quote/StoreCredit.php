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
namespace Bss\StoreCredit\Model\Quote;

use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\Quote\Address;
use Bss\StoreCredit\Model\CreditFactory;
use Bss\StoreCredit\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address\Total;

/**
 * Class StoreCredit
 */
class StoreCredit extends AbstractTotal
{
    /**
     * @var \Bss\StoreCredit\Model\Currency
     */
    protected $currency;

    /**
     * @var \Bss\StoreCredit\Helper\Data
     */
    private $bssStoreCreditHelper;

    /**
     * @var \Bss\StoreCredit\Model\CreditFactory
     */
    private $creditFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param CreditFactory $creditFactory
     * @param Data $bssStoreCreditHelper
     * @param StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Bss\StoreCredit\Model\Currency $currency,
        CreditFactory $creditFactory,
        Data $bssStoreCreditHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->currency = $currency;
        $this->bssStoreCreditHelper = $bssStoreCreditHelper;
        $this->creditFactory = $creditFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Collect store credit used
     *
     * @param Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param Total $total
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function collect(
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        if (!$this->isAllowUseCreditInvoice($quote, $shippingAssignment)) {
            return $this;
        }
        $baseGrandTotal = (float) $total->getBaseGrandTotal();
        $grandTotal = $total->getGrandTotal();
        $taxAmountUsed = 0;
        $baseTaxAmountUsed = 0;
        $shippingAmountUsed = 0;
        $baseShippingAmountUsed = 0;
        if ($baseGrandTotal) {
            if (!$this->bssStoreCreditHelper->getGeneralConfig('used_tax')) {
                $taxAmountUsed = $total->getTaxAmount();
                $baseTaxAmountUsed = $total->getBaseTaxAmount();
                $baseGrandTotal -= $baseTaxAmountUsed;
                $grandTotal -= $taxAmountUsed;
            }

            if (!$this->bssStoreCreditHelper->getGeneralConfig('used_shipping')) {
                $shippingAmountUsed = $total->getShippingAmount();
                $baseShippingAmountUsed = $total->getBaseShippingAmount();
                $baseGrandTotal -= $baseShippingAmountUsed;
                $grandTotal -= $shippingAmountUsed;
            }

            $quote->setBaseBssStorecreditAmount(0);
            $quote->setBssStorecreditAmount(0);
            $store = $this->storeManager->getStore($quote->getStoreId());
            $creditModel = $this->creditFactory->create();
            $creditModel->setWebsiteId($store->getWebsiteId());
            $convertAmountInput = $this->currency->convertAmountInput($quote);
            $baseBalanceInput =  $convertAmountInput[0];
            $balanceInput = $convertAmountInput[1];

            if ($baseBalanceInput >= $baseGrandTotal) {
                $baseBalanceUsedLeft = $baseGrandTotal;
                $balanceUsedLeft = $grandTotal;
                $total->setBaseGrandTotal($baseTaxAmountUsed + $baseShippingAmountUsed);
                $total->setGrandTotal($taxAmountUsed + $shippingAmountUsed);
            } else {
                $baseBalanceUsedLeft = $baseBalanceInput;
                $balanceUsedLeft = $balanceInput;
                $total->setBaseGrandTotal($total->getBaseGrandTotal() - $baseBalanceUsedLeft);
                $total->setGrandTotal($total->getGrandTotal() - $balanceUsedLeft);
            }

            $quote->setBaseBssStorecreditAmount($baseBalanceUsedLeft);
            $quote->setBssStorecreditAmount($balanceUsedLeft);
            $total->setBaseBssStorecreditAmount($baseBalanceUsedLeft);
            $total->setBssStorecreditAmount($balanceUsedLeft);
        }

        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @return bool
     */
    private function isAllowUseCreditInvoice($quote, $shippingAssignment)
    {
        $allow = true;
        if (!$quote->getId() || !$quote->getCustomerId() || !$this->bssStoreCreditHelper->getGeneralConfig('active')) {
            $allow =  false;
        }

        $address = $shippingAssignment->getShipping()->getAddress();
        if ($address->getAddressType() == Address::ADDRESS_TYPE_BILLING && !$quote->isVirtual()) {
            $allow = false;
        }

        if ($address->getAddressType() == Address::TYPE_SHIPPING && $quote->isVirtual()) {
            $allow = false;
        }
        return $allow;
    }

    /**
     * Add store credit information to address
     *
     * @param Quote $quote
     * @param Total $total
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(Quote $quote, Total $total)
    {
        if ($quote && $this->bssStoreCreditHelper->getGeneralConfig('active')) {
            $result = null;
            $amount = -$total->getBssStorecreditAmount();
            if ($amount != 0) {
                $result = [
                    'code' => 'bss_storecredit',
                    'title' => __('Store Credit'),
                    'value' => $amount
                ];
            }
            return $result;
        }
        return null;
    }
}
