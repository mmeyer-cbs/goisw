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
namespace Bss\QuoteExtension\Helper\QuoteExtension;

use Magento\Store\Model\ScopeInterface;

/**
 * Class Amount
 *
 * @package Bss\QuoteExtension\Helper\QuoteExtension
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Amount extends \Magento\Framework\App\Helper\AbstractHelper
{
    const PATH_REQUEST4QUOTE_MINIMUM_CUSTOMER_GROUP = 'bss_request4quote/request4quote_global/amount';
    const PATH_REQUEST4QUOTE_INCLUDE_TAX = 'sales/minimum_order/tax_including';

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $quoteExtensionSession;

    /**
     * @var \Bss\QuoteExtension\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Bss\QuoteExtension\Helper\Json
     */
    private $serializer;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * Amount constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Checkout\Model\Session $quoteExtensionSession
     * @param \Bss\QuoteExtension\Helper\Data $helperData
     * @param \Bss\QuoteExtension\Helper\Json $serializer
     * @param \Magento\Framework\Math\Random $mathRandom
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Session $quoteExtensionSession,
        \Bss\QuoteExtension\Helper\Data $helperData,
        \Bss\QuoteExtension\Helper\Json $serializer,
        \Magento\Framework\Math\Random $mathRandom
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->messageManager = $messageManager;
        $this->quoteExtensionSession = $quoteExtensionSession;
        $this->helperData = $helperData;
        $this->serializer = $serializer;
        $this->mathRandom = $mathRandom;
    }

    /**
     * Get Amount Data for customer group Admin Config
     *
     * @param int $store
     * @return bool|mixed
     */
    public function getAmountData($store = null)
    {
        $amountData = $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_MINIMUM_CUSTOMER_GROUP,
            ScopeInterface::SCOPE_STORE,
            $store
        );

        try {
            return $this->unserializeValue($amountData);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get Amount Data for customer group
     *
     * @param int $groupId
     * @param int $store
     * @return bool|float|int
     */
    public function getAmoutDataForCustomerGroup($groupId = null, $store = null)
    {
        $amountData = $this->getAmountData($store);
        if (!empty($amountData)) {
            foreach ($amountData as $configGroupId => $value) {
                if ($configGroupId == $groupId) {
                    $minAmount = isset($value['minimum_amount']) ? (float) $value['minimum_amount'] : 0;
                    return $minAmount;
                }
            }
        }

        return false;
    }

    /**
     * Get Message Notification
     *
     * @param int $groupId
     * @param int $store
     * @return mixed|string
     */
    public function getMessage($groupId = null, $store = null)
    {
        $amountData = $this->getAmountData($store);
        $message = '';
        $minAmount = '';
        if (!empty($amountData)) {
            foreach ($amountData as $configGroupId => $value) {
                if ($configGroupId == $groupId) {
                    if (isset($value['minimum_amount']) && $value['minimum_amount'] != '') {
                        $minAmount = (float) $value['minimum_amount'];
                        $minAmount = $this->helperData->formatCurrencyIncludeSymbol($minAmount);
                    }
                    if (isset($value['quote_message']) && $value['quote_message'] != '') {
                        $message = $value['quote_message'];
                    } else {
                        $message = __('Minimum quote amount is [min_amount]');
                    }
                }
            }
        }
        if ($minAmount != '') {
            $message = str_replace("[min_amount]", $minAmount, $message);
        } else {
            $message = '';
        }
        return $message;
    }

    /**
     * Get Customer group id
     *
     * @return int
     */
    public function getCustomerGroup()
    {
        return $this->customerSession->getCustomerGroupId();
    }

    /**
     * Get Tax Include In amount
     *
     * @param int $storeId
     * @return mixed
     */
    public function getTaxInclude($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_INCLUDE_TAX,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Validate Amount by quote address
     *
     * @param \Magento\Quote\Model\Quote\Address $addresses
     * @param int $customerGroupId
     * @return bool
     */
    public function validateQuoteAmount($addresses, $customerGroupId)
    {
        $enable = $this->helperData->isEnable();
        $minAmount = $this->getAmoutDataForCustomerGroup($customerGroupId);
        if (!$minAmount || !$enable) {
            return true;
        }

        $valid = false;
        foreach ($addresses as $address) {
            $taxInclude = $this->getTaxInclude();
            $taxes = $taxInclude ? $address->getBaseTaxAmount() : 0;
            if ($address->getBaseSubtotalWithDiscount() + $taxes >= $minAmount) {
                $valid = true;
            }
        }

        return $valid;
    }

    /**
     * Get Message Manager
     *
     * @return \Magento\Framework\Message\ManagerInterface
     */
    public function getMessageManager()
    {
        return $this->messageManager;
    }

    /**
     * Set Invalid Amount to request quote
     *
     * @param bool $status
     * @return void
     */
    public function setInvalidAmount($status)
    {
        return $this->quoteExtensionSession->setInvalidRequestQuoteAmount($status);
    }

    /**
     * Get Quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->quoteExtensionSession->getQuoteExtension();
    }

    /**
     * Make value readable by \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
     *
     * @param string|array $value
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function makeArrayFieldValue($value)
    {
        $value = $this->unserializeValue($value);
        if (!$this->isEncodedArrayFieldValue($value)) {
            $value = $this->encodeArrayFieldValue($value);
        }
        return $value;
    }

    /**
     * Create a value from a storable representation
     *
     * @param int|float|string $value
     * @return array
     */
    protected function unserializeValue($value)
    {
        if (is_string($value) && !empty($value)) {
            return $this->serializer->unserialize($value);
        } else {
            return [];
        }
    }

    /**
     * Check whether value is in form retrieved by _encodeArrayFieldValue()
     *
     * @param string|array $value
     * @return bool
     */
    protected function isEncodedArrayFieldValue($value)
    {
        if (!is_array($value)) {
            return false;
        }

        unset($value['__empty']);
        foreach ($value as $row) {
            if (!is_array($row)
                || !array_key_exists('customer_group', $row)
                || !array_key_exists('minimum_amount', $row)
                || !array_key_exists('quote_message', $row)
            ) {
                return false;
            }
        }
        return true;
    }

    /**
     * Encode value to be used in \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
     *
     * @param array $value
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function encodeArrayFieldValue(array $value)
    {
        $result = [];
        foreach ($value as $groupId => $val) {
            $resultId = $this->mathRandom->getUniqueHash('_');
            $result[$resultId] = [
                'customer_group' => $groupId,
                'minimum_amount' => $this->fixQty($val['minimum_amount']),
                'quote_message' => $val['quote_message']
            ];
        }
        return $result;
    }

    /**
     * Make value ready for store
     *
     * @param string|array $value
     * @return string
     */
    public function makeStorableArrayFieldValue($value)
    {
        if ($this->isEncodedArrayFieldValue($value)) {
            $value = $this->decodeArrayFieldValue($value);
        }
        $value = $this->serializeValue($value);
        return $value;
    }

    /**
     * Retrieve fixed qty value
     *
     * @param int|float|string|null $qty
     * @return float|null
     */
    protected function fixQty($qty)
    {
        return !empty($qty) ? (float) $qty : null;
    }

    /**
     * Decode value from used in \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
     *
     * @param array $value
     * @return array
     */
    protected function decodeArrayFieldValue(array $value)
    {
        $result = [];
        unset($value['__empty']);
        foreach ($value as $row) {
            if (!is_array($row)
                || !array_key_exists('customer_group', $row)
                || !array_key_exists('minimum_amount', $row)
                || !array_key_exists('quote_message', $row)
            ) {
                continue;
            }
            $groupId = $row['customer_group'];
            $minAmount = $this->fixQty($row['minimum_amount']);
            $quoteMess = $row['quote_message'];
            $result[$groupId] = [
                'minimum_amount' => $minAmount,
                'quote_message' => $quoteMess
            ];
        }

        return $result;
    }

    /**
     * Generate a storable representation of a value
     *
     * @param int|float|string|array $value
     * @return string
     */
    protected function serializeValue($value)
    {
        return $this->serializer->serialize($value);
    }
}
