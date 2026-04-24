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
namespace Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit;

/**
 * Class Form
 *
 * @package Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit
 */
class Form extends \Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit\Items
{
    /**
     * Json encoder
     *
     * @var \Bss\QuoteExtension\Helper\Json
     */
    protected $jsonEncoder;

    /**
     * Address service
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var string
     */
    protected $_template = "Magento_Sales::order/create/form.phtml";

    /**
     * @var \Bss\QuoteExtension\Helper\QuoteItems
     */
    protected $helperQuoteItems;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Bss\QuoteExtension\Helper\QuoteItems $helperQuoteItems
     * @param \Bss\QuoteExtension\Helper\Json $jsonEncoder
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Bss\QuoteExtension\Helper\QuoteItems $helperQuoteItems,
        \Bss\QuoteExtension\Helper\Json $jsonEncoder,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $orderCreate,
            $coreRegistry,
            $storeManagerInterface,
            $helperQuoteItems,
            $data
        );
        $this->jsonEncoder = $jsonEncoder;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('quote_extension_quote_edit_form');
    }

    /**
     * Retrieve url for loading blocks
     *
     * @return string
     */
    public function getLoadBlockUrl()
    {
        return $this->getUrl('bss_quote_extension/*/loadBlock');
    }

    /**
     * Retrieve url for form submiting
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('bss_quote_extension/*/save');
    }

    /**
     * Get customer selector display
     *
     * @return string
     */
    public function getCustomerSelectorDisplay()
    {
        $customerId = $this->getManageQuote()->getCustomerId();
        if ($customerId === null) {
            return 'block';
        }
        return 'none';
    }

    /**
     * Get store selector display
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreSelectorDisplay()
    {
        $storeId = $this->getStoreId();
        $customerId = $this->getManageQuote()->getCustomerId();
        if ($customerId !== null && !$storeId) {
            return 'block';
        }
        return 'none';
    }

    /**
     * Get data selector display
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDataSelectorDisplay()
    {
        $storeId = $this->getStoreId();
        $customerId = $this->getManageQuote()->getCustomerId();
        if ($customerId !== null && $storeId) {
            return 'block';
        }
        return 'none';
    }

    /**
     * Get order data jason
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrderDataJson()
    {
        $data = [];
        if ($this->getCustomerId() && $this->getManageQuote()->getCustomerId()) {
            $data['customer_id'] = $this->getManageQuote()->getCustomerId();
            $data['addresses'] = [];

            $addresses = $this->customerRepository->getById($this->getManageQuote()->getCustomerId())->getAddresses();

            foreach ($addresses as $address) {
                $addressForm = $this->helperQuoteItems->getAddressForm($address);
                $data['addresses'][$address->getId()] = $addressForm->outputData(
                    \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_JSON
                );
            }
        }
        if ($this->getStoreId() !== null) {
            $data['store_id'] = $this->getStoreId();
            $currency = $this->helperQuoteItems->getCurrency($this->getStore()->getCurrentCurrencyCode());
            $symbol = $currency->getSymbol() ? $currency->getSymbol() : $currency->getShortName();
            $data['currency_symbol'] = $symbol;
            $data['shipping_method_reseted'] = !(bool)$this->getQuote()->getShippingAddress()->getShippingMethod();
            $data['payment_method'] = $this->getQuote()->getPayment()->getMethod();
        }

        return $this->jsonEncoder->serialize($data);
    }
}
