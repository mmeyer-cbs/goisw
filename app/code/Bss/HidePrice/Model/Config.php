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
 * @package    Bss_HidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HidePrice\Model;

use Magento\Store\Model\ScopeInterface;
use Bss\HidePrice\Helper\Data;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Is enable module
     *
     * @param int|null $store
     * @return mixed
     */
    public function isEnable($store = null)
    {
        return $this->scopeConfig->getValue(
            Data::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve Selector
     *
     * @param int $store
     * @return string
     */
    public function getSelector($store = null)
    {
        $selector = $this->scopeConfig->getValue(
            Data::XML_PATH_SELECTOR,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        if ($selector == '') {
            $selector = '.action.tocart';
        }
        return $selector;
    }

    /**
     * Get config disable checkout
     *
     * @param int|null $store
     * @return mixed
     */
    public function getDisableCheckout($store = null)
    {
        return $this->scopeConfig->getValue(
            Data::XML_HIDE_PRICE_DISABLE_CHECKOUT_CONTROLLER,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve HidePrice Action
     *
     * @param int $store
     * @return string
     */
    public function getHidePriceAction($store = null)
    {
        return $this->scopeConfig->getValue(
            Data::XML_PATH_HIDE_PRICE_ACTION,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve HidePrice Categories
     *
     * @param int $store
     * @return string
     */
    public function getHidePriceCategories($store = null)
    {
        return $this->scopeConfig->getValue(
            Data::XML_HIDE_PRICE_CATEGORIES,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Retrieve HidePrice Customers
     *
     * @param int|null $store
     * @return string
     */
    public function getHidePriceCustomers($store = null)
    {
        return $this->scopeConfig->getValue(
            Data::XML_HIDE_PRICE_CUSTOMERS,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Hide Text
     *
     * @param object $product
     * @return string
     */
    public function getHidePriceText($product)
    {
        if ($product->getHidepriceMessage() && $product->getHidepriceAction() > 0) {
            return $product->getHidepriceMessage();
        } else {
            $_message = $this->scopeConfig->getValue(
                Data::XML_PATH_HIDE_PRICE_TEXT,
                ScopeInterface::SCOPE_STORE
            );
            if ($_message) {
                return $_message;
            } else {
                return __('Please contact us for price.');
            }
        }
    }

    /**
     * Get hide price text global
     *
     * @param int|null $storeId
     * @return string
     */
    public function getHidePriceTextGlobal($storeId = null)
    {
        return $this->scopeConfig->getValue(
            Data::XML_PATH_HIDE_PRICE_TEXT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get hide price url
     *
     * @param int|null $storeId
     * @return string
     */
    public function getHidePriceUrlConfig($storeId = null)
    {
        return $this->scopeConfig->getValue(
            Data::XML_PATH_HIDE_PRICE_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Hide Url
     *
     * @param object $product
     * @return string
     */
    public function getHidePriceUrl($product)
    {
        if (($product->getHidepriceAction() == 0
            || !$product->getHidepriceAction())) {
            $hidePriceUrl = $this->scopeConfig->getValue(
                Data::XML_PATH_HIDE_PRICE_URL,
                ScopeInterface::SCOPE_STORE
            );
            if (!empty($hidePriceUrl)) {
                return trim($hidePriceUrl);
            }
            return false;
        }
        if (!empty($product->getHidepriceUrl())) {
            return trim($product->getHidepriceUrl());
        }
        return false;
    }
}
