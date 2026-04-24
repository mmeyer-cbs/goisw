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
namespace Bss\CatalogPermission\Observer\Category;

use Bss\CatalogPermission\Model\Config\Source\BssListCmsPage;

/**
 * Class Product
 *
 * @package Bss\CatalogPermission\Observer\Category
 */
class Product implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Bss\CatalogPermission\Helper\ModuleConfig
     */
    protected $moduleConfig;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $response;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Bss\CatalogPermission\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Bss\CatalogPermission\Model\Product\Product
     */
    protected $product;

    /**
     * Product constructor.
     * @param \Bss\CatalogPermission\Helper\ModuleConfig $moduleConfig
     * @param \Magento\Framework\App\Response\Http $response
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Bss\CatalogPermission\Helper\Data $helperData
     * @param \Bss\CatalogPermission\Model\Product\Product $product
     */
    public function __construct(
        \Bss\CatalogPermission\Helper\ModuleConfig $moduleConfig,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Bss\CatalogPermission\Helper\Data $helperData,
        \Bss\CatalogPermission\Model\Product\Product $product
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->response = $response;
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->helperData = $helperData;
        $this->product = $product;
    }

    /**
     * Observer Execute
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $enableProductRestriction = $this->moduleConfig->enableProductRestricted();
        $enableModule = $this->moduleConfig->enableCatalogPermission();
        $useParentCategory = $this->moduleConfig->useParentCategory();
        if ($enableModule && $enableProductRestriction) {
            $postData = $observer->getRequest()->getParams();
            if (isset($postData['id'])) {
                $productId = $postData['id'];
                $productCategoryIds = $this->product->getProductCategories($productId);
                $currentStoreId = $this->storeManager->getStore()->getId();
                $customerGroupId = $this->helperData->getCustomerGroupId();
                $isProductPermission = (bool)$useParentCategory;
                $listBannedId = array_unique(
                    $this->helperData
                        ->getIdCategoryByCustomerGroupId($customerGroupId, $currentStoreId, $isProductPermission)
                );
                $baseUrl = $this->storeManager->getStore()->getBaseUrl();
                foreach ($productCategoryIds as $categoryId) {
                    if (in_array($categoryId, $listBannedId)) {
                        $this->response->setRedirect($baseUrl . 'catalogpermission/index/index?pagetype=product');
                    }
                }
            }
        }
        return $this;
    }
}
