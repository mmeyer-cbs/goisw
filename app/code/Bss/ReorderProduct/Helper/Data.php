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
 * @package    Bss_ReorderProduct
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ReorderProduct\Helper;

use Magento\Framework\App\Helper\Context;

/**
 * Class Data
 *
 * @package Bss\ReorderProduct\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var string
     */
    private $configSectionId = 'reorderproduct';

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $backendUrl;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $catalogHelperImg;

    /**
     * Data constructor.
     * @param Context $context
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Catalog\Helper\Image $catalogHelperImg
     */
    public function __construct(
        Context $context,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Catalog\Helper\Image $catalogHelperImg
    ) {
        $this->backendUrl = $backendUrl;
        $this->catalogHelperImg = $catalogHelperImg;
        parent::__construct($context);
    }

    /**
     * Get import url
     *
     * @return string
     */
    public function getImportUrl()
    {
        return $this->backendUrl->getUrl('reorderproduct/import/index');
    }

    /**
     * Get config flag
     *
     * @param string $path
     * @param string $store
     * @param string $scope
     * @return mixed
     */
    public function getConfigFlag($path, $store = null, $scope = null)
    {
        if ($scope === null) {
            $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        }
        return $this->scopeConfig->isSetFlag($path, $scope, $store);
    }

    /**
     * Get config value
     *
     * @param string $path
     * @param string $store
     * @param string $scope
     * @return string
     */
    public function getConfigValue($path, $store = null, $scope = null)
    {
        if ($scope === null) {
            $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        }
        return $this->scopeConfig->getValue($path, $scope, $store);
    }

    /**
     * Is active module config
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->getConfigFlag($this->configSectionId . '/general/active');
    }

    /**
     * Is redirect to cart page after add to cart config
     *
     * @return bool
     */
    public function isRedirecttocart()
    {
        return $this->getConfigFlag($this->configSectionId . '/general/redirect_cart');
    }

    /**
     * Is redirect to cart page after add to wishlist config
     *
     * @return bool
     */
    public function isRedirecttowishlist()
    {
        return $this->getConfigFlag($this->configSectionId . '/general/redirect_wishlist');
    }

    /**
     * Is disable buy request param config
     *
     * @return string|null
     */
    public function getIgnoreBuyRquestParam()
    {
        return $this->getConfigValue($this->configSectionId . '/general/ignore_buy_request');
    }

    /**
     * Is show button add to wishlist config
     *
     * @return bool
     */
    public function showbtnWishlist()
    {
        return $this->getConfigFlag($this->configSectionId . '/list_reoderproduct/btnwishlist');
    }

    /**
     * Is show columns config
     *
     * @return mixed
     */
    public function showColumns()
    {
        return $this->getConfigValue($this->configSectionId . '/list_reoderproduct/show_columns');
    }

    /**
     * Is show btn quick view config
     *
     * @return mixed
     */
    public function showbtnQuickview()
    {
        return $this->getConfigValue($this->configSectionId . '/list_reoderproduct/show_quickview');
    }

    /**
     * Is show sku config
     *
     * @return bool
     */
    public function showSku()
    {
        return $this->getConfigFlag($this->configSectionId . '/list_reoderproduct/show_sku');
    }

    /**
     * Is show qty inventory config
     *
     * @return bool
     */
    public function showQtyInventory()
    {
        return $this->getConfigFlag($this->configSectionId . '/list_reoderproduct/qty_inventory');
    }

    /**
     * Get value items per page config value
     *
     * @return mixed
     */
    public function getListperpagevalue()
    {
        return $this->getConfigValue($this->configSectionId . '/list_reoderproduct/list_per_page_values');
    }

    /**
     * Get value items per page config value
     *
     * @return mixed
     */
    public function getListperpage()
    {
        return $this->getConfigValue($this->configSectionId . '/list_reoderproduct/list_per_page');
    }

    /**
     * Get config sort by value
     *
     * @return mixed
     */
    public function getSortby()
    {
        return $this->getConfigValue($this->configSectionId . '/list_reoderproduct/sort_by');
    }

    /**
     * Is show all list
     *
     * @return bool
     */
    public function showAlllist()
    {
        return $this->getConfigFlag($this->configSectionId . '/list_reoderproduct/list_allow_all');
    }

    /**
     * Get catalog helper img image
     *
     * @return \Magento\Catalog\Helper\Image
     */
    public function getCatalogHelperImg()
    {
        return $this->catalogHelperImg;
    }
}
