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
 * @package    Bss_CatalogPermission
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CatalogPermission\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ModuleConfig
 *
 * @package Bss\CatalogPermission\Helper
 */
class ModuleConfig extends AbstractHelper
{
    const ENABLE_CMS_PAGE = 'catalog_permission/cms_general/enable_cms_page';
    const ENABLE_CATALOG_PERMISSION = 'catalog_permission/general/enable_catalog';
    const CUSTOM_CMS_PAGE_URL = 'catalog_permission/cms_general/url_link';
    const CUSTOM_CATALOG_PAGE_URL = 'catalog_permission/general/url_link';
    const CMS_ERROR_MESSAGE = 'catalog_permission/cms_general/cms_error_message';
    const CATALOG_ERROR_MESSAGE = 'catalog_permission/general/error_message';
    const CMS_REDIRECT_PAGE = 'catalog_permission/cms_general/cms_redirect_page';
    const CATALOG_REDIRECT_PAGE = 'catalog_permission/general/redirect_page';
    const ENABLE_PRODUCT_RESTRICT = 'catalog_permission/general/enable_product_restricted';
    const USE_PARENT_CATEGORY = 'catalog_permission/general/use_parent_category';
    const DISABLE_CATEGORY_LINK = 'catalog_permission/general/disable_categories_link';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * ModuleConfig constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
    }

    /**
     * Get enable Catalog Permission
     *
     * @param int $storeId
     * @return bool
     */
    public function enableCatalogPermission($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::ENABLE_CATALOG_PERMISSION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get config page redirect
     *
     * @param int $storeId
     * @return int
     */
    public function getPageIdToRedirect($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::CATALOG_REDIRECT_PAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Page id in CMS redirect
     *
     * @param int $storeId
     * @return int
     */
    public function getPageIdToRedirectCms($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::CMS_REDIRECT_PAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get config disable link
     *
     * @param int $storeId
     * @return bool
     */
    public function disableCategoryLink($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::DISABLE_CATEGORY_LINK,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get config use parent category
     *
     * @param int $storeId
     * @return bool
     */
    public function useParentCategory($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::USE_PARENT_CATEGORY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get error message
     *
     * @param int $storeId
     * @return string
     */
    public function getErrorMessage($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::CATALOG_ERROR_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get enable product restricted
     *
     * @param int $storeId
     * @return mixed
     */
    public function enableProductRestricted($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::ENABLE_PRODUCT_RESTRICT,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get enable CMS permission
     *
     * @param int $storeId
     * @return bool
     */
    public function enableCmsPagePermission($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::ENABLE_CMS_PAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get error message in CMS page permission
     *
     * @param int $storeId
     * @return string
     */
    public function getErrorMessageCms($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::CMS_ERROR_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get custom url to redirect
     *
     * @param int $storeId
     * @return string
     */
    public function getCustomPageUrl($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::CUSTOM_CMS_PAGE_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get custom url to redirect
     *
     * @param int $storeId
     * @return string
     */
    public function getCustomCatalogUrl($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::CUSTOM_CATALOG_PAGE_URL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
