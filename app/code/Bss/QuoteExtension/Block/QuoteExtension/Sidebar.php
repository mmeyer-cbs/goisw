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

use Magento\Store\Model\ScopeInterface;

/**
 * Cart sidebar block
 *
 * @api
 * @codingStandardsIgnoreFile
 */
class Sidebar extends AbstractQuoteExtension
{
    /**
     * Xml pah to checkout sidebar display value
     */
    const XML_PATH_CHECKOUT_SIDEBAR_DISPLAY = 'checkout/sidebar/display';

    /**
     * Xml pah to checkout sidebar count value
     */
    const XML_PATH_CHECKOUT_SIDEBAR_COUNT = 'checkout/sidebar/count';

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Bss\QuoteExtension\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Bss\QuoteExtension\Helper\Json
     */
    private $serializer;

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Bss\QuoteExtension\Model\Session $quoteExtensionSession
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Bss\QuoteExtension\Helper\Data $helperData
     * @param \Magento\Customer\CustomerData\JsLayoutDataProviderPoolInterface $jsLayoutDataProvider
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param array $data
     * @param \Bss\QuoteExtension\Helper\Json|null $serializer
     * @throws \RuntimeException
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Bss\QuoteExtension\Model\Session $quoteExtensionSession,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Bss\QuoteExtension\Helper\Data $helperData,
        \Magento\Customer\CustomerData\JsLayoutDataProviderPoolInterface $jsLayoutDataProvider,
        \Magento\Tax\Model\Config $taxConfig,
        array $data = [],
        \Bss\QuoteExtension\Helper\Json $serializer = null
    ) {
        if (isset($data['jsLayout'])) {
            $this->jsLayout = array_merge_recursive($jsLayoutDataProvider->getData(), $data['jsLayout']);
            unset($data['jsLayout']);
        } else {
            $this->jsLayout = $jsLayoutDataProvider->getData();
        }
        parent::__construct($context, $customerSession, $quoteExtensionSession, $data);
        $this->imageHelper = $imageHelper;
        $this->helperData = $helperData;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Bss\QuoteExtension\Helper\Json::class);
        $this->taxConfig = $taxConfig;
    }

    /**
     * Set config show tax in quote page
     *
     * @return string
     */
    public function getJsLayout()
    {
        $jsLayout = $this->jsLayout;
        if (isset($jsLayout['components']['miniquote_content']['children']['subtotal.container']['children']['subtotal']['children']['subtotal.totals'])) {
            $jsLayout['components']['miniquote_content']['children']['subtotal.container']['children']['subtotal']
            ['children']['subtotal.totals']['config']['display_quote_subtotal_incl_tax'] = (int)$this->taxConfig->displayCartSubtotalInclTax();
            $jsLayout['components']['miniquote_content']['children']['subtotal.container']['children']['subtotal']
            ['children']['subtotal.totals']['config']['display_quote_subtotal_excl_tax'] = (int)$this->taxConfig->displayCartSubtotalExclTax();
        }
        return $this->serializer->serialize($jsLayout);
    }

    /**
     * Returns minicart config
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig()
    {
        return [
            'quoteExtensionUrl' => $this->getShoppingCartUrl(),
            'updateItemQtyUrl' => $this->getUpdateItemQtyUrl(),
            'removeItemUrl' => $this->getRemoveItemUrl(),
            'imageTemplate' => $this->getImageHtmlTemplate(),
            'baseUrl' => $this->getBaseUrl(),
            'minicartMaxItemsVisible' => $this->getMiniCartMaxItemsCount(),
            'websiteId' => $this->_storeManager->getStore()->getWebsiteId(),
            'maxItemsToDisplay' => $this->getMaxItemsToDisplay(),
            'clearQuote' => $this->getUrlClearQuote()
        ];
    }

    /**
     * Json Encode Config
     *
     * @return bool|false|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSerializedConfig()
    {
        return $this->serializer->serialize($this->getConfig());
    }

    /**
     * Get Prodct Image
     *
     * @return string
     */
    public function getImageHtmlTemplate()
    {
        return $this->imageHelper->getFrame()
            ? 'Magento_Catalog/product/image'
            : 'Magento_Catalog/product/image_with_borders';
    }

    /**
     * Get shopping cart page url
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getShoppingCartUrl()
    {
        return $this->getUrl('quoteextension/quote');
    }

    /**
     * Get clear quote url
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getUrlClearQuote()
    {
        return $this->getUrl('quoteextension/quote/clearQuote', ['_secure' => true]);
    }

    /**
     * Get update cart item url
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getUpdateItemQtyUrl()
    {
        return $this->getUrl('quoteextension/sidebar/updateItemQty', ['_secure' => true]);
    }

    /**
     * Get remove cart item url
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getRemoveItemUrl()
    {
        return $this->getUrl('quoteextension/quote/clearQuote', ['_secure' => true]);
    }


    /**
     * Define if Mini Shopping Cart Pop-Up Menu enabled
     *
     * @return bool
     */
    public function isNeedToDisplaySideBar()
    {
        return (bool)$this->_scopeConfig->getValue(
            self::XML_PATH_CHECKOUT_SIDEBAR_DISPLAY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Return totals from custom quote if needed
     *
     * @return array
     */
    public function getTotalsCache()
    {
        if (empty($this->totals)) {
            $quote = $this->getCustomQuote() ? $this->getCustomQuote() : $this->getQuote();
            $this->totals = $quote->getTotals();
        }
        return $this->totals;
    }

    /**
     * Return base url.
     *
     * @codeCoverageIgnore
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * Return max visible item count for minicart
     *
     * @return int
     */
    private function getMiniCartMaxItemsCount()
    {
        return (int)$this->_scopeConfig->getValue('checkout/sidebar/count', ScopeInterface::SCOPE_STORE);
    }

    /**
     * Returns maximum cart items to display
     *
     * This setting regulates how many items will be displayed in minicart
     *
     * @return int
     */
    private function getMaxItemsToDisplay()
    {
        return (int)$this->_scopeConfig->getValue(
            'checkout/sidebar/max_items_display_count',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get request quote icon
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getIcon()
    {
        return $this->helperData->getIcon();
    }

    /**
     * Is enable
     *
     * @return bool
     */
    public function isEnable()
    {
        return $this->helperData->isEnable();
    }
}
