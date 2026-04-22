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
namespace Bss\QuoteExtension\Helper\Admin;

use Magento\Store\Model\ScopeInterface;

/**
 * Class StyleConfig
 *
 * @package Bss\QuoteExtension\Helper
 */
class ConfigShow extends \Magento\Framework\App\Helper\AbstractHelper
{
    const PATH_REQUEST4QUOTE_ENABLED_PRODUCT_PAGE = 'bss_request4quote/request4quote_product_page_config/enable';
    const PATH_REQUEST4QUOTE_PRODUCT_PAGE_TEXT = 'bss_request4quote/request4quote_product_page_config/text';
    const PATH_REQUEST4QUOTE_PRODUCT_PAGE_CUSTOM_STYLE = 'bss_request4quote/request4quote_product_page_config/style';
    const PATH_REQUEST4QUOTE_ENABLED_OTHER_PAGE = 'bss_request4quote/request4quote_product_other_page_config/enable';
    const PATH_REQUEST4QUOTE_OTHER_PAGE_TEXT = 'bss_request4quote/request4quote_product_other_page_config/text';
    const PATH_REQUEST4QUOTE_OTHER_PAGE_STYLE = 'bss_request4quote/request4quote_product_other_page_config/style';

    /**
     * Is enable add to quote button for product page
     *
     * @param int $store
     * @return bool
     */
    public function isEnableProductPage($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::PATH_REQUEST4QUOTE_ENABLED_PRODUCT_PAGE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get product page quote button text
     *
     * @param int $store
     * @return mixed
     */
    public function getProductPageText($store = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_PRODUCT_PAGE_TEXT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get product page custom style
     *
     * @param int $store
     * @return mixed
     */
    public function getProductPageCustomStyle($store = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_PRODUCT_PAGE_CUSTOM_STYLE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Is enable for other page
     *
     * @param int $store
     * @return bool
     */
    public function isEnableOtherPage($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::PATH_REQUEST4QUOTE_ENABLED_OTHER_PAGE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get other page quote button text
     *
     * @param int $store
     * @return mixed
     */
    public function getOtherPageText($store = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_OTHER_PAGE_TEXT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get other page custom style
     *
     * @param int $store
     * @return mixed
     */
    public function getOtherPageCustomStyle($store = null)
    {
        return $this->scopeConfig->getValue(
            self::PATH_REQUEST4QUOTE_OTHER_PAGE_STYLE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
