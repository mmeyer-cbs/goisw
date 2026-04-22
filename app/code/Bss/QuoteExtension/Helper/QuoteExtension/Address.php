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
namespace Bss\QuoteExtension\Helper\QuoteExtension;

use Magento\Customer\Model\SessionFactory as CustomerSession;
use Magento\Quote\Api\CartItemRepositoryInterface as QuoteItemRepository;
use Magento\Quote\Api\ShippingMethodManagementInterface as ShippingMethodManager;
use Bss\QuoteExtension\Helper\Data;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\AddressRegistry;

/**
 * Class Address
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Address extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var QuoteItemRepository
     */
    protected $quoteItemRepository;

    /**
     * @var ShippingMethodManager
     */
    protected $shippingMethodManager;

    /**
     * @var \Bss\QuoteExtension\Helper\Json
     */
    protected $jsonSerializer;

    /**
     * @var \Magento\Quote\Model\Quote\Address\ToOrderAddress $quoteAddressToOrderAddress
     */
    protected $quoteAddressToOrderAddress;

    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer
     */
    protected $addressRenderer;

    /**
     * Tax data
     *
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxData;

    /**
     * @var AddressRegistry
     */
    protected $addressRegistry;

    /**
     * Address constructor.
     * @param CustomerSession $customerSession
     * @param \Magento\Framework\App\Helper\Context $context
     * @param QuoteItemRepository $quoteItemRepository
     * @param ShippingMethodManager $shippingMethodManager
     * @param \Bss\QuoteExtension\Helper\Json $jsonSerializer
     * @param \Magento\Quote\Model\Quote\Address\ToOrderAddress $quoteAddressToOrderAddress
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Tax\Helper\Data $taxData
     * @param AddressRegistry $addressRegistry
     */
    public function __construct(
        CustomerSession $customerSession,
        \Magento\Framework\App\Helper\Context $context,
        QuoteItemRepository $quoteItemRepository,
        ShippingMethodManager $shippingMethodManager,
        \Bss\QuoteExtension\Helper\Json $jsonSerializer,
        \Magento\Quote\Model\Quote\Address\ToOrderAddress $quoteAddressToOrderAddress,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Tax\Helper\Data $taxData,
        AddressRegistry $addressRegistry
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context);
        $this->quoteItemRepository = $quoteItemRepository;
        $this->shippingMethodManager = $shippingMethodManager;
        $this->jsonSerializer = $jsonSerializer;
        $this->quoteAddressToOrderAddress = $quoteAddressToOrderAddress;
        $this->addressRenderer = $addressRenderer;
        $this->taxData = $taxData;
        $this->addressRegistry = $addressRegistry;
    }

    /**
     * Return Required Address Config
     *
     * @param int $store
     * @return bool
     */
    public function isRequiredAddress($store = null)
    {
        if ($this->customerSession->create()->isLoggedIn()) {
            return $this->scopeConfig->isSetFlag(
                Data::PATH_REQUEST4QUOTE_SHIPPING_REQUIRED,
                ScopeInterface::SCOPE_STORE,
                $store
            );
        }
        return false;
    }

    /**
     * Get List Items by quote Id
     *
     * @param int $quoteId
     * @return \Magento\Quote\Api\Data\CartItemInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getListItemsById($quoteId)
    {
        return $this->quoteItemRepository->getList($quoteId);
    }

    /**
     * Get shipping methods by quote Id
     *
     * @param int $quoteId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function getShippindMethods($quoteId)
    {
        return $this->shippingMethodManager->get($quoteId);
    }

    /**
     * Json Data Config.
     *
     * @param array $data
     * @return bool|false|string
     */
    public function jsonEncodeDataConfig($data)
    {
        return $this->jsonSerializer->serialize($data);
    }

    /**
     * Convert Address
     *
     * @param string $type
     * @param \Bss\QuoteExtension\Model\Quote $quote
     * @return string|null
     */
    public function formatAddress($type, $quote)
    {
        if ($type == 'shipping') {
            $salesAddress = $this->quoteAddressToOrderAddress->convert($quote->getShippingAddress());
            if (!$salesAddress->getFirstname()) {
                return null;
            }
            return $this->addressRenderer->format($salesAddress, 'html');
        }
        $salesAddress = $this->quoteAddressToOrderAddress->convert($quote->getBillingAddress());
        if (!$salesAddress->getFirstname()) {
            return null;
        }
        return $this->addressRenderer->format($salesAddress, 'html');
    }

    /**
     * Check Resubmit Quote Enable
     *
     * @param int $store
     * @return bool
     */
    public function disableResubmit($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            Data::PATH_REQUEST4QUOTE_DISABLE_RESUBMIT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @return \Magento\Tax\Helper\Data
     */
    public function getTaxHelper()
    {
        return $this->taxData;
    }

    /**
     * Get customer address by address id
     *
     * @param int $addressId
     * @return \Magento\Customer\Model\Address
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomerAddress($addressId)
    {
        return $this->addressRegistry->retrieve($addressId);
    }
}
