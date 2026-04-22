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
namespace Bss\StoreCredit\Controller\Cart;

use Bss\StoreCredit\Helper\Data;
use Bss\StoreCredit\Model\CreditFactory;
use Magento\Checkout\Model\CartFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class UpdatePost
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class UpdatePost extends Action
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
     * Sales quote repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\Checkout\Model\CartFactory
     */
    private $cartFactory;

    /**
     * @param Context $context
     * @param CartFactory $cartFactory
     * @param Data $bssStoreCreditHelper
     * @param CreditFactory $creditFactory
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        \Bss\StoreCredit\Model\Currency  $currency,
        Context $context,
        CartFactory $cartFactory,
        Data $bssStoreCreditHelper,
        CreditFactory $creditFactory,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->currency = $currency;
        parent::__construct($context);
        $this->cartFactory = $cartFactory;
        $this->bssStoreCreditHelper = $bssStoreCreditHelper;
        $this->creditFactory = $creditFactory;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Used store credit action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        if ($this->bssStoreCreditHelper->getGeneralConfig('cart_page_display')) {
            $remove = $this->getRequest()->getParam('remove');
            $amount = (float) $this->getRequest()->getParam('bss_store_credit');
            $cartQuote = $this->cartFactory->create()->getQuote();
            $amount = $this->currency->round($amount);
            $totals_amount = 0;

            if ($remove) {
                $amount = 0;
            }

            $baseAmount = $this->currency->round($this->currency->convertCurrency($amount, $cartQuote->getQuoteCurrencyCode(), $cartQuote->getBaseCurrencyCode()));
            $creditModel = $this->creditFactory->create();

            $message = $this->bssStoreCreditHelper->getSuccessApplyCreditMsg();

            $this->getTotalsAmount($cartQuote, $totals_amount);

            if ($amount < 0 || !$cartQuote->getId() || !$creditModel->validateBalance($cartQuote, $baseAmount)) {
                $this->messageManager->addErrorMessage(__('Something went wrong. Please enter a value again'));
            } elseif ($baseAmount > $totals_amount) {
                $cartQuote->setBaseBssStorecreditAmountInput($totals_amount);
                $cartQuote->setBssStorecreditAmountInput($totals_amount);
                $cartQuote->setStoreCreditCurrencyCode($cartQuote->getQuoteCurrencyCode());
                $cartQuote->collectTotals();
                $this->quoteRepository->save($cartQuote);
                $this->messageManager->addSuccessMessage($message);
            } else {
                $cartQuote->setBaseBssStorecreditAmountInput($baseAmount);
                $cartQuote->setBssStorecreditAmountInput($amount);
                $cartQuote->setStoreCreditCurrencyCode($cartQuote->getQuoteCurrencyCode());
                $cartQuote->collectTotals();
                $this->quoteRepository->save($cartQuote);
                $this->messageManager->addSuccessMessage($message);
            }
        }
        return $this->_redirect($this->_redirect->getRefererUrl());
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param float $totals_amount
     * @return float
     */
    private function getTotalsAmount($quote, &$totals_amount)
    {
        $subTotal = 0;
        $totals_amount = $quote->getData('subtotal_with_discount');
        $shippingAmount = (float) $this->getRequest()->getParam('shipping_amount');
        $taxAmount = (float) $this->getRequest()->getParam('tax_amount');
        if ($shippingAmount && $this->bssStoreCreditHelper->getGeneralConfig('used_shipping')) {
            $totals_amount += $shippingAmount;
        }

        if ($taxAmount && $this->bssStoreCreditHelper->getGeneralConfig('used_tax')) {
            $totals_amount += $taxAmount;
        }

        return $subTotal;
    }
}
