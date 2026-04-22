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
namespace Bss\QuoteExtension\Block\QuoteExtension;

use Bss\QuoteExtension\Block\QuoteExtension\Submit\LayoutProcessor;
use Bss\QuoteExtension\Helper\CartHidePrice;
use Bss\QuoteExtension\Helper\QuoteExtension\Address;
use Exception;
use Magento\Checkout\Model\CompositeConfigProvider;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Model\Cart\TotalSegment;
use Magento\Quote\Model\Quote\Item;

/**
 * Class Submit
 *
 * @package Bss\QuoteExtension\Block\QuoteExtension
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Submit extends AbstractQuoteExtension
{
    /**
     * @var array
     */
    protected $layoutProcessors;

    /**
     * @var CompositeConfigProvider
     */
    protected $configProvider;

    /**
     * @var LayoutProcessor
     */
    protected $layoutProcessor;

    /**
     * @var Address
     */
    protected $helperAddress;

    /**
     * @var CartTotalRepositoryInterface
     */
    protected $cartTotalRepository;

    /**
     * @var CartHidePrice
     */
    protected $cartHidePrice;

    /**
     * Submit constructor.
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param Session $checkoutSession
     * @param CompositeConfigProvider $configProvider
     * @param Submit\LayoutProcessor $layoutProcessor
     * @param Address $helperAddress
     * @param CartTotalRepositoryInterface $cartTotalRepository
     * @param CartHidePrice $cartHidePrice
     * @param array $layoutProcessors
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        Session $checkoutSession,
        CompositeConfigProvider $configProvider,
        Submit\LayoutProcessor $layoutProcessor,
        Address  $helperAddress,
        CartTotalRepositoryInterface $cartTotalRepository,
        CartHidePrice $cartHidePrice,
        array $layoutProcessors = [],
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $checkoutSession, $data);
        $this->layoutProcessors = $layoutProcessors;
        $this->configProvider = $configProvider;
        $this->layoutProcessor = $layoutProcessor;
        $this->helperAddress = $helperAddress;
        $this->cartTotalRepository = $cartTotalRepository;
        $this->cartHidePrice = $cartHidePrice;
    }

    /**
     * Json Encode Layout
     *
     * @return false|string
     * @throws LocalizedException
     */
    public function getJsLayout()
    {
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }

        $this->jsLayout = $this->layoutProcessor->process($this->jsLayout);

        if ($this->customerSession->getCustomerId()) {
            $this->unsetPersonalInformation();
        } else {
            $this->unsetShippingAddressMethod();
        }

        if (!$this->canShowSubtotal()) {
           $this->unsetBlockTotal();
        }
        return $this->helperAddress->jsonEncodeDataConfig($this->jsLayout);
    }

    /**
     * Unset shipping address and shipping method when customer not login
     */
    public function unsetShippingAddressMethod()
    {
        unset($this->jsLayout['components']
            ['block-submit']['children']['steps']['children']["shipping-step"]['children']['step-config']);
        unset($this->jsLayout['components']
            ['block-submit']['children']['steps']['children']["shipping-step"]['children']['shippingAddress']);
    }

    /**
     * Not display logic submit quote with non customer
     */
    public function unsetPersonalInformation()
    {
        unset($this->jsLayout['components']
            ['block-submit']['children']['steps']['children']["shipping-step"]['children']['customer-email']);
        unset($this->jsLayout['components']
            ['block-submit']['children']['steps']['children']["shipping-step"]['children']['add-login-quote-button']);
        unset($this->jsLayout['components']
            ['block-submit']['children']['steps']['children']["shipping-step"]['children']['personal-information']);
    }

    /**
     * Unset block total when hide price
     */
    public function unsetBlockTotal()
    {
        unset($this->jsLayout['components']
            ['block-submit']['children']['steps']['children']["shipping-step"]['children']['block-totals']
            ['children']['subtotal']);
        unset($this->jsLayout['components']
            ['block-submit']['children']['steps']['children']["shipping-step"]['children']['block-totals']
            ['children']['grand-total']);
    }

    /**
     * Get checkout config
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCheckoutConfig()
    {
        $output = $this->configProvider->getConfig();
        $output['quoteData'] = $this->getQuoteData();
        $output['quoteItemData'] = $this->getQuoteItemData();
        $output['selectedShippingMethod'] = $this->getSelectedShippingMethod();
        $output['storeCode'] = $this->getStoreCode();
        $output['staticBaseUrl'] = $this->getStaticBaseUrl();
        $output['totalsData'] = $this->getTotalsData();
        if (isset($output['checkoutUrl'])) {
            $output['checkoutUrl'] = $this->getUrl('quoteextension/quote/');
        }
        if (isset($output['isGuestCheckoutAllowed'])) {
            $output['isGuestCheckoutAllowed'] = false;
        }
        if (isset($output['defaultSuccessPageUrl'])) {
            $output['defaultSuccessPageUrl'] = $this->getUrl('quoteextension/quote/success');
        }
        $output['isRequiredAddress'] = $this->isRequiredAddress();
        $output['inValidAmount'] = $this->quoteExtensionSession->getInvalidRequestQuoteAmount();
        $output["addToQuote"] = true;
        return $output;
    }

    /**
     * Address is required
     *
     * @return bool
     */
    /**
     * Get quote Data
     *
     * @return array
     */
    protected function getQuoteData()
    {
        $quoteData = [];
        if ($this->getQuoteExtension()->getId()) {
            $quoteData = $this->getQuoteExtension()->toArray();
            $quoteData['is_virtual'] = $this->getQuoteExtension()->getIsVirtual();
        }
        return $quoteData;
    }

    /**
     * Get Quote item data
     *
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getQuoteItemData()
    {
        $quoteItemData = [];
        $quoteId = $this->getQuoteExtension()->getId();
        if ($quoteId) {
            $quoteItems = $this->helperAddress->getListItemsById($quoteId);
            foreach ($quoteItems as $index => $quoteItem) {
                $quoteItemData[$index] = $quoteItem->toArray();
                $quoteItemData[$index]['options'] = $this->getFormattedOptionValue($quoteItem);
            }
        }
        return $quoteItemData;
    }

    /**
     * Get shipping methods
     *
     * @return array|null
     */
    protected function getSelectedShippingMethod()
    {
        $shippingMethodData = null;
        try {
            $quoteId = $this->getQuoteExtension()->getId();
            $shippingMethod = $this->helperAddress->getShippindMethods($quoteId);
            if ($shippingMethod) {
                $shippingMethodData = $shippingMethod->__toArray();
            }
        } catch (Exception $exception) {
            $shippingMethodData = null;
        }
        return $shippingMethodData;
    }

    /**
     * Retrieve store code
     *
     * @return string
     * @codeCoverageIgnore
     */
    protected function getStoreCode()
    {
        return $this->getQuoteExtension()->getStore()->getCode();
    }

    /**
     * Get Static Base Url
     *
     * @return string
     */
    protected function getStaticBaseUrl()
    {
        return $this->getQuoteExtension()->getStore()->getBaseUrl(UrlInterface::URL_TYPE_STATIC);
    }

    /**
     * Shipping Address is required\
     *
     * @return bool
     */
    public function isRequiredAddress()
    {
        return $this->helperAddress->isRequiredAddress();
    }

    /**
     * Return quote totals data
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getTotalsData()
    {
        /** @var TotalsInterface $totals */
        $totals = $this->cartTotalRepository->get($this->getQuoteExtension()->getId());
        $items = [];
        /** @var  \Magento\Quote\Model\Cart\Totals\Item $item */
        foreach ($totals->getItems() as $item) {
            $items[] = $item->__toArray();
        }
        $totalSegmentsData = [];
        /** @var TotalSegment $totalSegment */
        foreach ($totals->getTotalSegments() as $totalSegment) {
            $totalSegmentArray = $totalSegment->toArray();
            if (is_object($totalSegment->getExtensionAttributes())) {
                $totalSegmentArray['extension_attributes'] = $totalSegment->getExtensionAttributes()->__toArray();
            }
            $totalSegmentsData[] = $totalSegmentArray;
        }
        $totals->setItems($items);
        $totals->setTotalSegments($totalSegmentsData);
        $totalsArray = $totals->toArray();
        if (is_object($totals->getExtensionAttributes())) {
            $totalsArray['extension_attributes'] = $totals->getExtensionAttributes()->__toArray();
        }
        return $totalsArray;
    }

    /**
     * Can show subtotal
     *
     * @return bool
     * @throws LocalizedException
     */
    protected function canShowSubtotal()
    {
        foreach ($this->getQuoteExtension()->getAllVisibleItems() as $item) {
            /* @var $item Item */
            if ($item->getProductType() == 'configurable') {
                $parentProductId = $item->getProductId();
                $childProductSku = $item->getSku();
                $canShowPrice = $this->cartHidePrice->canShowPrice($parentProductId, $childProductSku);
            } else {
                $canShowPrice = $this->cartHidePrice->canShowPrice($item->getProductId(), false);
            }
            if (!$canShowPrice) {
                return false;
            }
        }
        return true;
    }
}
