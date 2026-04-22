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
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Model;

use Bss\QuoteExtension\Api\ManagementInterface;
use Bss\QuoteExtension\Helper\Admin\ConfigShow;
use Bss\QuoteExtension\Helper\Customer\AutoLogging;
use Bss\QuoteExtension\Helper\Data as HelperData;
use Bss\QuoteExtension\Helper\QuoteExtension\Amount;

/**
 * Class Management
 */
class Management implements ManagementInterface
{
    /**
     * @var ConfigShow
     */
    protected $configShow;

    /**
     * @var AutoLogging
     */
    protected $autoLogging;

    /**
     * @var \Bss\QuoteExtension\Helper\QuoteExtension\ExpiredQuote
     */
    protected $expiredQuote;

    /**
     * @var Amount
     */
    protected $amount;

    /**
     * @var Label
     */
    protected $label;

    /**
     * @var QuoteCustomerGroupId
     */
    protected $quoteCustomerGroupId;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * Management constructor.
     *
     * @param HelperData $helperData
     */
    public function __construct(
        ConfigShow $configShow,
        AutoLogging $autoLogging,
        \Bss\QuoteExtension\Helper\QuoteExtension\ExpiredQuote $expiredQuote,
        Amount $amount,
        \Bss\QuoteExtension\Model\Label $label,
        \Bss\QuoteExtension\Model\QuoteCustomerGroupId $quoteCustomerGroupId,
        HelperData $helperData
    ) {
        $this->configShow = $configShow;
        $this->autoLogging = $autoLogging;
        $this->expiredQuote = $expiredQuote;
        $this->amount = $amount;
        $this->label = $label;
        $this->quoteCustomerGroupId = $quoteCustomerGroupId;
        $this->helperData = $helperData;
    }

    /**
     * @inheritDoc
     */
    public function getConfigByStoreId($storeId = null)
    {
        $configQuotable = $this->helperData->getQuotable($storeId);
        return [
            "configs" => [
                "general" => [
                    "enable" => (bool)$this->helperData->isEnable($storeId),
                    "enable_change_price_customer_group" => $this->quoteCustomerGroupId->isEnableConfigSaveCustomer()
                ],
                "request4quote_global" => [
                    "enable_add_quote_for_all_products" => [
                        "value" => $configQuotable,
                        "label" => $this->label->getLabelQuotable($configQuotable)
                    ],
                    "apply_for_customer" => $this->helperData->getApplyForCustomers($storeId),
                    "validate_qty_quoted_products" => $this->helperData->validateQuantity($storeId),
                    "amount_data" => $this->amount->getAmountData($storeId),
                    "default_expired_days" => $this->expiredQuote->getDefaultExpiredDays($storeId),
                    "reminder_days" => $this->expiredQuote->getReminderDays($storeId),
                    "enable_quote_items_comment" => $this->helperData->isEnableQuoteItemsComment($storeId),
                    "required_shipping_address" => $this->helperData->isRequiredAddress($storeId),
                    "disable_resubmit_action" => $this->helperData->disableResubmit($storeId),
                    "icon_mini_quote" => $this->helperData->getIcon($storeId),
                    "auto_login_customer" => $this->autoLogging->isAutoLogging($storeId)
                ],
                "product_page" => [
                    "enable" => $this->configShow->isEnableProductPage($storeId),
                    "text_button_quote" => $this->configShow->getProductPageText($storeId),
                    "custom_type" => $this->configShow->getProductPageCustomStyle($storeId),
                ],
                "other_page" => [
                    "enable" => $this->configShow->isEnableOtherPage($storeId),
                    "text_button_quote" => $this->configShow->getOtherPageText($storeId),
                    "custom_type" => $this->configShow->getOtherPageCustomStyle($storeId),
                ]
            ]

        ];
    }

    /**
     * Get config module
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->getConfigByStoreId();
    }
}
