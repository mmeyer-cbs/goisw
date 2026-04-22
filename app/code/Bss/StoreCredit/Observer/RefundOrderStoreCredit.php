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

namespace Bss\StoreCredit\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Bss\StoreCredit\Model\CreditFactory;
use Bss\StoreCredit\Helper\Data;
use Bss\StoreCredit\Api\StoreCreditRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Event\Observer;

/**
 * Class RefundOrderStoreCredit
 */
class RefundOrderStoreCredit implements ObserverInterface
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
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Bss\StoreCredit\Model\CreditFactory
     */
    private $creditFactory;

    /**
     * @var \Bss\StoreCredit\Api\StoreCreditRepositoryInterface
     */
    private $storeCreditRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param RequestInterface $request
     * @param CreditFactory $creditFactory
     * @param Data $bssStoreCreditHelper
     * @param StoreCreditRepositoryInterface $storeCreditRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Bss\StoreCredit\Model\Currency $currency,
        RequestInterface $request,
        CreditFactory $creditFactory,
        Data $bssStoreCreditHelper,
        StoreCreditRepositoryInterface $storeCreditRepository,
        LoggerInterface $logger
    ) {
        $this->currency = $currency;
        $this->request = $request;
        $this->creditFactory = $creditFactory;
        $this->bssStoreCreditHelper = $bssStoreCreditHelper;
        $this->storeCreditRepository = $storeCreditRepository;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $params = $this->request->getParams();
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $customerId = $creditmemo->getCustomerId();
        if ($customerId) {
            try {
                $websiteId = $creditmemo->getStore()->getWebsiteId();
                $credit = $this->storeCreditRepository->get($customerId, $websiteId);
                $baseGrandTotal = $creditmemo->getBaseGrandTotal();
                $grandTotal = $creditmemo->getGrandTotal();
                $creditModel = $this->creditFactory->create();
                if (isset($params['creditmemo']['storecredit']) && $params['creditmemo']['storecredit']) {
                    $baseStorecreditRefund = $creditmemo->getBaseBssStorecreditAmount() + $baseGrandTotal;
                } else {
                    $baseStorecreditRefund = $creditmemo->getBaseBssStorecreditAmount();
                }

            if ($creditmemo->getBaseBssStorecreditAmountRefund()) {
                return;
            }
            $creditCurrencyCode = $this->currency->getCreditCurrencyCode($creditModel->getCurrencyCode(), $websiteId) ;
            $baseStorecreditRefund = $this->currency->convertCurrency($baseStorecreditRefund, $creditmemo->getBaseCurrencyCode(), $creditCurrencyCode);

            if ($credit->getId()) {
                $baseAmountUpdate = $credit->getBalanceAmount() + $baseStorecreditRefund;
                $credit->setBalanceAmount($baseAmountUpdate)
                    ->save();
            } else {
                $baseAmountUpdate = (float)$baseStorecreditRefund;
                $creditModel->setCustomerId($customerId)
                    ->setBalanceAmount($baseAmountUpdate)
                    ->setWebsiteId($websiteId)
                    ->setCurrencyCode($creditCurrencyCode)
                    ->save();
            }
            if ($baseStorecreditRefund) {
                if (isset($params['creditmemo']['storecredit']) && $params['creditmemo']['storecredit']) {
                    $bssStorecreditAmount = $creditmemo->getBssStorecreditAmount() + $grandTotal;
                } else {
                    $bssStorecreditAmount = $creditmemo->getBssStorecreditAmount();
                }

                    $creditmemo->setBaseBssStorecreditAmountRefund($baseStorecreditRefund)
                        ->setBssStorecreditAmountRefund($bssStorecreditAmount);
                }
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
