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
 * @package    Bss_AddMultipleProducts
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\AddMultipleProducts\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Group;
use Bss\AddMultipleProducts\Helper\Data as HelperData;

/**
 * Class ConfigData
 * @package Bss\AddMultipleProducts\Block
 */
class ConfigData extends \Magento\Framework\View\Element\Template
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * ConfigData constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Bss\AddMultipleProducts\Helper\Data $helperData
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        HelperData $helperData,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->customerSession = $customerSession;
        $this->taxConfig = $taxConfig;
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return int
     */
    public function getGroupCustomerId()
    {
        $customer = $this->customerSession->create();
        $group_Id = 0;
        if ($customer->isLoggedIn()) {
            $group_Id = $customer->getCustomer()->getGroupId();
        }
        return $group_Id;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function priceIncludesTax()
    {
        return $this->taxConfig->priceIncludesTax($this->_storeManager->getStore());
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUrlMediaStick()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCurrentCurrency()
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCurrencySymbol();
    }

    /**
     * @return string
     */
    public function geturlAddToCart()
    {
        return $this->getUrl('addmuntiple/cart/add');
    }

    /**
     * @return string
     */
    public function geturlAddMultipleToCart()
    {
        return $this->getUrl('addmuntiple/cart/addMuntiple');
    }

    /**
     * @return string | bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBackgroundStick()
    {
        if ($this->helperData->backGroundStick()) {
            return $this->getUrlMediaStick().'addmunltipleproduct/stick/'.$this->helperData->backGroundStick();
        }
        return false;
    }

    /**
     * Get class apply add multiple cart or quote
     *
     * @return array
     */
    public function applyClass() {
        return array_unique(
            explode(
            ",", $this->helperData->displayAddMultipleCart() . "," . $this->helperData->displayAddMultipleQuote())
        );
    }

    /**
     * Get class display add multiple cart
     *
     * @return string
     */
    public function displayAddMultipleCart() {
        return "," . $this->helperData->displayAddMultipleCart() . ",";
    }

    /**
     * Get class display add multiple quote
     *
     * @return string
     */
    public function displayAddMultipleQuote() {
        return "," . $this->helperData->displayAddMultipleQuote() . ",";
    }

    /**
     * Get current category id
     *
     * @return false|int
     */
    public function getCurrentCategoryId() {
        $categoryCurrent = $this->_registry->registry('current_category');
        if ($categoryCurrent && $categoryCurrent->getId()) {
            return $categoryCurrent->getId();
        }
        return false;

    }

    /**
     * Check forbid category pages
     *
     * @return bool
     */
    public function forbidCategoryPages()
    {
        if ($cateID = $this->getCurrentCategoryId()) {
            $cateID = "," . $cateID . ",";
            $getConfigForbidCategoryPages = "," . $this->helperData->getConfigForbidCategoryPages() . ",";

            if (strpos($getConfigForbidCategoryPages, $cateID) !== false) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        if (!$this->helperData->isEnabled()) {
            return false;
        }

        if (!$this->forbidCategoryPages()) {
            return false;
        }

        $customerGroups = !empty($this->getCustomerGroup())? $this->getCustomerGroup() : [];
        if (in_array(Group::CUST_GROUP_ALL, $customerGroups)
            || in_array($this->getGroupCustomerId(), $customerGroups)) {
            return true;
        }

        return false;
    }

    /**
     * @return array|bool
     */
    public function getCustomerGroup()
    {
        return $this->helperData->getCustomerGroup();
    }

    /**
     * @return bool
     */
    public function showSelectProduct()
    {
        return $this->helperData->showSelectProduct();
    }

    /**
     * @return bool
     */
    public function showStick()
    {
        return $this->helperData->showStick();
    }

    /**
     * @return mixed
     */
    public function positionButton()
    {
        return $this->helperData->positionButton();
    }

    /**
     * @return mixed|string
     */
    public function getTextbuttonaddmt()
    {
        return $this->helperData->getTextbuttonaddmt();
    }

    /**
     * @return int
     */
    public function defaultQty()
    {
        return $this->helperData->defaultQty();
    }

    /**
     * @return bool
     */
    public function showTotal()
    {
        return $this->helperData->showTotal();
    }

    /**
     * Is enable for other page of quote_extension
     *
     * @return bool
     */
    public function isEnableOtherPageQuoteExtension()
    {
       return $this->helperData->isEnableOtherPageQuoteExtension();
    }

    /**
     * Get url add to quote
     *
     * @return string
     */
    public function getUrlAddToQuote()
    {
        return $this->getUrl('quoteextension/quote/adda');
    }

    /**
     * Get url add multiple to q    uote
     *
     * @return string
     */
    public function getUrlAddMultipleToQuote()
    {
        return $this->getUrl('quoteextension/quote/addmultiple');
    }
}
