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
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CatalogPermission\Observer\Category;

use Bss\CatalogPermission\Helper\Data;
use Bss\CatalogPermission\Helper\ModuleConfig;
use Magento\Framework\Event\Observer;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ProductAdd
 *
 * @package Bss\CatalogPermission\Observer\Category
 */
class ProductAdd implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var ModuleConfig
     */
    protected $moduleConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * CatalogPermissionHelper constructor.
     *
     * @param ModuleConfig $moduleConfig
     * @param Data $helperData
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ModuleConfig $moduleConfig,
        Data $helperData,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        $this->moduleConfig = $moduleConfig;
        $this->helperData = $helperData;
        $this->customerSessionFactory = $customerSessionFactory;
    }

    /**
     * If product was prevent to access
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        try {
            $enableProductRestriction = $this->moduleConfig->enableProductRestricted();
            $enableModule = $this->moduleConfig->enableCatalogPermission();
            if ($enableModule && $enableProductRestriction && $product) {
                $productCategoryIds = $product->getCategoryIds();
                $currentStoreId = $this->storeManager->getStore()->getId();
                $customerGroupId = $this->customerSessionFactory->create()->getCustomerGroupId();
                $bannedCategories = array_unique(
                    $this->helperData
                        ->getIdCategoryByCustomerGroupId($customerGroupId, $currentStoreId, true)
                );
                foreach ($productCategoryIds as $categoryId) {
                    if (in_array($categoryId, $bannedCategories)) {
                        $product->setCantAccess(true);
                    }
                }
            }
        } catch (\Exception $e) {
            $product->setCantAccess(null);
        }
    }
}
