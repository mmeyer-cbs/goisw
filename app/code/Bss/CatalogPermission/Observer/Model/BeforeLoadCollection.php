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
namespace Bss\CatalogPermission\Observer\Model;

use Bss\CatalogPermission\Helper\Data;
use Bss\CatalogPermission\Helper\ModuleConfig;
use Bss\CatalogPermission\Model\ResourceModel\Category;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Collection
 *
 * @package Bss\CatalogPermission\Observer\Model
 */
class BeforeLoadCollection implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Bss\CatalogPermission\Helper\Data
     */
    protected $helperData;
    /**
     * @var \Bss\CatalogPermission\Helper\ModuleConfig
     */
    protected $moduleConfig;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Bss\CatalogPermission\Model\ResourceModel\Category
     */
    private $categoryResource;

    /**
     * Collection constructor.
     * @param ModuleConfig $moduleConfig
     * @param Data $helperData
     * @param StoreManagerInterface $storeManager
     * @param Category $categoryResource
     */
    public function __construct(
        ModuleConfig $moduleConfig,
        Data $helperData,
        StoreManagerInterface $storeManager,
        \Bss\CatalogPermission\Model\ResourceModel\Category $categoryResource
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->helperData = $helperData;
        $this->storeManager = $storeManager;
        $this->categoryResource = $categoryResource;
    }

    /**
     * Get Category Collection
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $disableCategoryLink = $this->moduleConfig->disableCategoryLink();
        $enableCatalogPermission = $this->moduleConfig->enableCatalogPermission();
        if ($disableCategoryLink && $enableCatalogPermission) {
            $customerGroupId = $this->helperData->getCustomerGroupId();
            $currentStoreId = $this->storeManager->getStore()->getId();
            $arrIds = $this->helperData->getIdCategoryByCustomerGroupId(
                $customerGroupId,
                $currentStoreId,
                false
            );
            if ($arrIds) {
                $observer->getCategoryCollection()->addAttributeToFilter(
                    $this->categoryResource->getColumnNameId(),
                    ['nin' => $arrIds]);
            }
        }
    }
}
