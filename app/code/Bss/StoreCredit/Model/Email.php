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
namespace Bss\StoreCredit\Model;

use Magento\Framework\Mail\Template\TransportBuilder;
use Bss\StoreCredit\Helper\Data as StoreCreditData;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Area;

/**
 * Class Email
 * @package Bss\StoreCredit\Model
 */
class Email
{
    /**
     * @var Currency
     */
    protected $currency;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var \Bss\StoreCredit\Helper\Data
     */
    private $storeCreditData;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $priceHelper;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    private $inlineTranslation;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    /**
     * @param TransportBuilder $transportBuilder
     * @param StoreCreditData $storeCreditData
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param Data $priceHelper
     * @param StateInterface $inlineTranslation
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        \Bss\StoreCredit\Model\Currency $currency,
        TransportBuilder $transportBuilder,
        StoreCreditData $storeCreditData,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        Data $priceHelper,
        StateInterface $inlineTranslation,
        CustomerFactory $customerFactory
    ) {
        $this->currency = $currency;
        $this->transportBuilder = $transportBuilder;
        $this->storeCreditData = $storeCreditData;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->priceHelper = $priceHelper;
        $this->inlineTranslation = $inlineTranslation;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @param int|null $storeId
     * @param int $customerId
     * @param null $credit
     * @param string|null $comment
     */
    public function sendMailNotify($storeId, $customerId, $credit = null, $comment = null,  $dataCustomer= null)
    {
        $currencyWebsite = $this->currency->getCurrencyCodeByWebsite($credit->getWebsiteId());
        $currencyStoreView= $this->currency->getCurrencyCodeStoreView($storeId);
        try {
            $this->inlineTranslation->suspend();
            $store = $this->storeManager->getStore($storeId);
            $balanceChange = '';
            $balanceAmount = '';
            $balanceAmountWebsite = '';
            $balanceChangeWebsite = '';
            if ($credit) {
                $balanceChange = $this->currency->convertAndFormatPriceCurrency($credit->getChangeAmount(), $currencyWebsite, $currencyStoreView);
                $balanceAmount = $this->currency->convertAndFormatPriceCurrency($credit->getBalanceAmount(), $currencyWebsite, $currencyStoreView);

                if ($currencyWebsite != $currencyStoreView) {
                    $balanceChangeWebsite = "(" . $this->currency->formatPrice($credit->getChangeAmount(), $currencyWebsite) . ")";
                    $balanceAmountWebsite = "(" . $this->currency->formatPrice($credit->getBalanceAmount(), $currencyWebsite) . ")";
                }
            }
            $customer = $this->customerFactory->create()->load($customerId);
            $customerName = $customer->getName();
            $customerEmail = $customer->getEmail();
            if ($customer === null) {
                $customerName = $dataCustomer['customerName'];
                $customerEmail = $dataCustomer['customerEmail'];
            }
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($this->storeCreditData->getEmailConfig('template', $storeId))
                ->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => $storeId
                    ]
                )
                ->setTemplateVars(
                    [
                        'store' => $store,
                        'customer' => $customerName,
                        'comment' => $comment,
                        'balance_change' => $balanceChange,
                        'balance_amount' => $balanceAmount,
                        'balance_change_website' => $balanceChangeWebsite,
                        'balance_amount_website' => $balanceAmountWebsite
                    ]
                )
                ->setFrom($this->storeCreditData->getEmailConfig('identity', $storeId))
                ->addTo($customerEmail, $customerName)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
