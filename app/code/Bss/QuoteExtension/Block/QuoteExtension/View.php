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

namespace Bss\QuoteExtension\Block\QuoteExtension;

use Bss\QuoteExtension\Helper\Data as QuoteHelper;
use Bss\QuoteExtension\Helper\QuoteExtension\Version as QuoteVersionHelper;
use Bss\QuoteExtension\Model\Config\Source\Status;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Tax\Helper\Data as TaxHelper;

/**
 * Class View
 *
 * @package Bss\QuoteExtension\Block\QuoteExtension
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class View extends AbstractQuoteExtension
{
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var quoteHelper
     */
    protected $helper;

    /**
     * @var QuoteVersionHelper
     */
    protected $versionHelper;

    /**
     * @var TaxHelper
     */
    protected $taxHelper;

    /**
     * View constructor.
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param Registry $coreRegistry
     * @param QuoteHelper $helper
     * @param QuoteVersionHelper $versionHelper
     * @param TaxHelper $taxHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        Registry $coreRegistry,
        QuoteHelper $helper,
        QuoteVersionHelper $versionHelper,
        TaxHelper $taxHelper,
        array $data = []
    ) {

        $this->coreRegistry = $coreRegistry;
        $this->helper = $helper;
        $this->versionHelper = $versionHelper;
        $this->taxHelper = $taxHelper;
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $data
        );
    }

    /**
     * Prepare Quote Item Product URLs
     *
     * @codeCoverageIgnore
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->prepareItemUrls();
    }

    /**
     * Get active quote
     *
     * @return \Bss\QuoteExtension\Model\Quote|mixed|null
     */
    public function getQuoteExtension()
    {
        if (null === $this->quote) {
            $this->quote = $this->coreRegistry->registry('current_quote');
        }
        return $this->quote;
    }

    /**
     * Prepare cart items URLs
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareItemUrls()
    {
        $products = [];
        foreach ($this->getItems() as $item) {
            $product = $item->getProduct();
            $option = $item->getOptionByCode('product_type');
            if ($option) {
                $product = $option->getProduct();
            }

            if ($item->getStoreId() != $this->_storeManager->getStore()->getId() &&
                !$item->getRedirectUrl() &&
                !$product->isVisibleInSiteVisibility()
            ) {
                $products[$product->getId()] = $item->getStoreId();
            }
        }

        if ($products) {
            $products = $this->versionHelper->getProducts($products);
            foreach ($this->getItems() as $item) {
                $product = $item->getProduct();
                $option = $item->getOptionByCode('product_type');
                $this->getProductByOption($option);

                if (isset($products[$product->getId()])) {
                    $object = $this->versionHelper->initDataObject($products[$product->getId()]);
                    $item->getProduct()->setUrlDataObject($object);
                }
            }
        }
    }

    /**
     * Get product from option
     *
     * @param object $option
     * @return mixed
     */
    protected function getProductByOption($option)
    {
        if ($option) {
            $product = $option->getProduct();
        }
        return $product;
    }

    /**
     * Quote has error
     *
     * @return bool
     */
    public function hasError()
    {
        return $this->getQuoteExtension()->getHasError();
    }

    /**
     * Item Summary Qty
     *
     * @return int
     */
    public function getItemsSummaryQty()
    {
        return $this->getQuoteExtension()->getItemsSummaryQty();
    }

    /**
     * Return customer quote items
     *
     * @return array
     */
    public function getItems()
    {
        if ($this->getCustomItems()) {
            return $this->getCustomItems();
        }

        return parent::getItems();
    }

    /**
     * Get Request Quote count item
     *
     * @codeCoverageIgnore
     * @return int
     */
    public function getItemsCount()
    {
        return $this->getQuoteExtension()->getItemsCount();
    }

    /**
     * Get Customer
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomer()
    {
        if ($this->customerSession->isLoggedIn()) {
            return $this->helper->getCustomerById($this->customerSession->getCustomerId());
        } else {
            return null;
        }
    }

    /**
     * Retrieve current order model instance
     *
     * @return mixed
     */
    public function getRequestQuote()
    {
        return $this->coreRegistry->registry('current_quote_extension');
    }

    /**
     * Get request quote history collection
     *
     * @return \Bss\QuoteExtension\Model\ResourceModel\QuoteVersion\Collection
     */
    public function getHistoryCollection()
    {
        $requestQuote = $this->getRequestQuote();
        return $this->versionHelper->getHistoryCollection($requestQuote);
    }

    /**
     * Retrieve formated price
     *
     * @param float $value
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function formatPrice($value)
    {
        return $this->helper->formatPrice($value);
    }

    /**
     * Get enable module
     *
     * @return bool
     */
    public function isEnable()
    {
        return $this->helper->isEnable();
    }

    /**
     * Can Submit Quote
     *
     * @return bool
     */
    public function canSubmitQuote()
    {
        $disableResubmit = $this->helper->disableResubmit();
        $status = $this->getRequestQuote()->getStatus();
        if (!$disableResubmit) {
            $statusCanEdit = [
                Status::STATE_UPDATED,
                Status::STATE_REJECTED,
                Status::STATE_EXPIRED
            ];
        } else {
            $statusCanEdit = [
                Status::STATE_UPDATED
            ];
        }
        return in_array($status, $statusCanEdit);
    }

    /**
     * Prepare Log For item
     *
     * @param string $log
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareLogItems($log)
    {
        return $this->versionHelper->prepareLogHtml($log);
    }

    /**
     * Url Submit Quote
     *
     * @return string
     */
    public function getSubmitViewQuote()
    {
        return $this->getUrl('quoteextension/quote/viewSubmit');
    }

    /**
     * @return QuoteHelper
     */
    public function getModuleHelper()
    {
        return $this->helper;
    }

    /**
     * @return TaxHelper
     */
    public function getTaxHelper()
    {
        return $this->taxHelper;
    }

    /**
     * Check if history data has comment or not
     *
     * @param array $historyData
     * @return bool
     */
    public function isNoHistoryComment($historyData)
    {
        return array_reduce($historyData, [$this, "noCommentChecking"], false);
    }

    /**
     * Callback func to check history comment
     *
     * @param bool $carryVal Holds the return value of the previous iteration;
     * in the case of the first iteration it instead holds the value of initial.
     * @param array $item Holds the value of the current iteration.
     * @return bool
     * @
     */
    protected function noCommentChecking($carryVal, $item)
    {
        return $carryVal || $item['comment'] !== null;
    }
}
