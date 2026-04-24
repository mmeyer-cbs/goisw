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
namespace Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Quote info block
 *
 * Class Info
 */
class Info extends \Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\AbstractQuote
{
    /**
     * @var \Bss\QuoteExtension\Model\Customer
     */
    protected $customer;

    /**
     * Customer service
     *
     * @var \Magento\Customer\Api\CustomerMetadataInterface
     */
    protected $metadata;

    /**
     * Metadata element factory
     *
     * @var \Magento\Customer\Model\Metadata\ElementFactory
     */
    protected $metadataElementFactory;

    /**
     * @var \Bss\QuoteExtension\Helper\Admin\Edit\Info
     */
    protected $helperInfo;

    /**
     * Info constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param \Magento\Customer\Api\CustomerMetadataInterface $metadata
     * @param \Magento\Customer\Model\Metadata\ElementFactory $elementFactory
     * @param \Bss\QuoteExtension\Helper\Admin\Edit\Info $helperInfo
     * @param array $data
     */
    public function __construct(
        \Bss\QuoteExtension\Model\Customer $customer,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Magento\Customer\Api\CustomerMetadataInterface $metadata,
        \Magento\Customer\Model\Metadata\ElementFactory $elementFactory,
        \Bss\QuoteExtension\Helper\Admin\Edit\Info $helperInfo,
        array $data = []
    ) {
        $this->customer = $customer;
        $this->metadata = $metadata;
        $this->metadataElementFactory = $elementFactory;
        $this->helperInfo = $helperInfo;
        parent::__construct($context, $registry, $adminHelper, $data);
    }

    /**
     * Get quote store name
     *
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getQuoteStoreName()
    {
        if ($this->getQuote()) {
            $storeId = $this->getQuote()->getStoreId();
            if ($storeId === null) {
                $deleted = __(' [deleted]');
                return nl2br($this->getQuote()->getStoreName()) . $deleted;
            }
            $store = $this->_storeManager->getStore($storeId);
            $name = [$store->getWebsite()->getName(), $store->getGroup()->getName(), $store->getName()];
            return implode('<br/>', $name);
        }

        return null;
    }

    /**
     * Retrieve quote model instance
     *
     * @return \Bss\QuoteExtension\Model\ManageQuote
     */
    public function getQuoteExtension()
    {
        return $this->coreRegistry->registry('quoteextension_quote');
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getMageQuote()
    {
        return $this->coreRegistry->registry('mage_quote');
    }

    /**
     * Return name of the customer group.
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerGroupName()
    {
        if ($this->getQuote()) {
            $customerGroupId = $this->getQuote()->getCustomerGroupId();
            try {
                if ($customerGroupId !== null) {
                    return $this->helperInfo->getCustomerGroupByCode($customerGroupId);
                }
            } catch (NoSuchEntityException $e) {
                return '';
            }
        }

        return '';
    }

    /**
     * Get URL to edit the customer.
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerViewUrl()
    {
        if (!$this->getQuote()->getCustomerId()) {
            return '';
        }

        return $this->getUrl('customer/index/edit', ['id' => $this->getQuote()->getCustomerId()]);
    }

    /**
     * Get quote view URL.
     *
     * @param  int $quoteId
     * @return string
     */
    public function getViewUrl($quoteId)
    {
        return $this->getUrl('quoteextension/quote/view', ['quote_id' => $quoteId]);
    }

    /**
     * Return array of additional account data
     *
     * Value is option style array
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerAccountData()
    {
        $accountData = [];
        $entityType = 'customer';

        /** @var \Magento\Customer\Api\Data\AttributeMetadataInterface $attribute */
        foreach ($this->metadata->getAllAttributesMetadata() as $attribute) {
            if (!$attribute->isVisible() || $attribute->isSystem()) {
                continue;
            }
            $quoteKey = sprintf('customer_%s', $attribute->getAttributeCode());
            $quoteValue = $this->getQuote()->getData($quoteKey);
            if ($quoteValue != '') {
                $metadataElement = $this->metadataElementFactory->create($attribute, $quoteValue, $entityType);
                $value = $metadataElement->outputValue($this->helperInfo->returnOutPutHtml());
                $sortOrder = $attribute->getSortOrder() + $attribute->isUserDefined() ? 200 : 0;
                $sortOrder = $this->_prepareAccountDataSortOrder($accountData, $sortOrder);
                $accountData[$sortOrder] = [
                    'label' => $attribute->getFrontendLabel(),
                    'value' => $this->escapeHtml($value, ['br']),
                ];
            }
        }
        ksort($accountData, SORT_NUMERIC);

        return $accountData;
    }

    /**
     * Find sort quote for account data
     *
     * Sort Order used as array key
     *
     * @param  array $data
     * @param  int $sortOrder
     * @return int
     */
    protected function _prepareAccountDataSortOrder(array $data, $sortOrder)
    {
        if (isset($data[$sortOrder])) {
            return $this->_prepareAccountDataSortOrder($data, $sortOrder + 1);
        }

        return $sortOrder;
    }

    /**
     * Whether Customer IP address should be displayed on sales documents
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function shouldDisplayCustomerIp()
    {
        return !$this->_scopeConfig->isSetFlag(
            'sales/general/hide_customer_ip',
            $this->helperInfo->returnScopeStoreView(),
            $this->getQuote()->getStoreId()
        );
    }

    /**
     * Check if is single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }

    /**
     * Get object created at date affected with object store timezone
     *
     * @param  mixed $store
     * @param  string $createdAt
     * @return \DateTime
     */
    public function getCreatedAtStoreDate($store, $createdAt)
    {
        return $this->_localeDate->scopeDate($store, $createdAt, true);
    }

    /**
     * Get object created at date
     *
     * @param string $createdAt
     * @return \DateTime
     * @throws \Exception
     */
    public function getQuoteAdminDate($createdAt)
    {
        return $this->_localeDate->date($createdAt);
    }

    /**
     * Returns string with formatted address
     *
     * @param  string $type
     * @return null|string
     * @throws \Exception
     */
    public function getFormattedAddress($type)
    {
        return $this->helperInfo->formatAddress($type, $this->getQuote());
    }

    /**
     * Returns Order view url is based on quotation id
     *
     * @param  int $quoteId
     * @return array
     */
    public function getOrderViewUrlByQuotationId($quoteId)
    {
        //get order view url based on quotation id
        $orderIds = $this->helperInfo->getOrderIds($quoteId);

        $orderUrls = [];
        foreach ($orderIds as $val) {
            $orderUrls[$val->getIncrementId()] = $this->getUrl(
                'sales/order/view',
                [
                    'order_id' => $val->getEntityId()
                ]
            );
        }

        return $orderUrls;
    }

    /**
     * Retrieve required options from parent
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function _beforeToHtml()
    {
        if (!$this->getParentBlock()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Please correct the parent block for this block.')
            );
        }
        $this->setQuote($this->getParentBlock()->getQuote());

        foreach ($this->getParentBlock()->getQuoteInfoData() as $key => $value) {
            $this->setDataUsingMethod($key, $value);
        }

        parent::_beforeToHtml();
    }

    /**
     * Get date format:
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->_localeDate->getDateFormat();
    }

    /**
     * Get customer name
     *
     * @return int|mixed|string
     */
    public function getCustomerName()
    {
        $quoteExtension = $this->getQuoteExtension();
        $customerName = null;
        if ($quoteExtension->getCustomerId()) {
            $customerName = $this->customer->getCustomerNameById($quoteExtension->getCustomerId());
        }
        return $customerName ? $customerName : $quoteExtension->getData('customer_name');
    }
}
