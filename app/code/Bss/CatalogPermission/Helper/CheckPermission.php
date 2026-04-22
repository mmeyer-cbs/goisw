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
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CatalogPermission\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Class CheckPermission
 * @package Bss\CatalogPermission\Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckPermission extends AbstractHelper
{
    /**
     * @var \Magento\UrlRewrite\Model\UrlRewriteFactory
     */
    protected $urlRewriteFactory;
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var ModuleConfig
     */
    protected $moduleConfig;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var \Bss\CatalogPermission\Model\Product\Product
     */
    protected $product;

    /**
     * CheckPermission constructor.
     *
     * @param Context $context
     * @param \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param Data $helperData
     * @param ModuleConfig $moduleConfig
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Bss\CatalogPermission\Model\Product\Product $product
     */
    public function __construct(
        Context $context,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewriteFactory,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Bss\CatalogPermission\Helper\Data $helperData,
        \Bss\CatalogPermission\Helper\ModuleConfig $moduleConfig,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Bss\CatalogPermission\Model\Product\Product $product
    ) {
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->pageFactory = $pageFactory;
        $this->helperData = $helperData;
        $this->moduleConfig = $moduleConfig;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->product = $product;
        parent::__construct($context);
    }

    /**
     * Check permission custom url
     *
     * @param string $url
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkCustomUrl($url)
    {
        $urlRewrite = $this->urlRewriteFactory->create();
        $urlRewrite->load($url, 'request_path');
        $entityType = $urlRewrite->getEntityType();
        $entityId = $urlRewrite->getEntityId();
        $customerGroupId = $this->customerSession->create()->getCustomerGroupId();
        $currentStoreId = $this->storeManager->getStore()->getId();
        if ($entityType == "cms-page") {
            return $this->checkPermissionCmsPage($customerGroupId, $entityId);
        }
        if ($entityType == "category") {
            return $this->checkPermissionCategory($customerGroupId, $currentStoreId, $entityId);
        }
        if ($entityType == "product") {
            return $this->checkPermissionProduct($customerGroupId, $currentStoreId, $entityId);
        }
        return false;
    }

    /**
     * Check permission for product url
     *
     * @param int $customerGroupId
     * @param int $currentStoreId
     * @param int $productId
     * @return bool
     */
    public function checkPermissionProduct($customerGroupId, $currentStoreId, $productId)
    {
        $listBannedId = array_unique(
            $this->helperData
                ->getIdCategoryByCustomerGroupId($customerGroupId, $currentStoreId, true)
        );
        $productCategoryIds = $this->product->getProductCategories($productId);
        foreach ($productCategoryIds as $categoryId) {
            if (in_array($categoryId, $listBannedId)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check permission for category url
     *
     * @param int $customerGroupId
     * @param int $currentStoreId
     * @param int $categoryId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkPermissionCategory($customerGroupId, $currentStoreId, $categoryId)
    {
        $listIdSubCategory = $this->helperData->getIdCategoryByCustomerGroupId(
            $customerGroupId,
            $currentStoreId,
            false
        );
        $useParentCategory = $this->moduleConfig->useParentCategory();
        if ($useParentCategory && in_array($categoryId, $listIdSubCategory)) {
            return true;
        }
        $data = $this->categoryRepository->get($categoryId, $currentStoreId);
        $arrCustomerGroup = $data['bss_customer_group'];
        if (is_array($arrCustomerGroup) && in_array($customerGroupId, $arrCustomerGroup)) {
            return true;
        }
        return false;
    }

    /**
     * Check permission for cms url
     *
     * @param int $customerGroupId
     * @param int $pageId
     * @return bool
     */
    public function checkPermissionCmsPage($customerGroupId, $pageId)
    {
        $page = $this->pageFactory->create()->load($pageId);
        $data = $page->getData();
        $arrCustomerGroup = $data['bss_customer_group'] ?
            $this->helperData->returnJson()->unserialize($data['bss_customer_group']) : false;

        if (is_array($arrCustomerGroup) && in_array($customerGroupId, $arrCustomerGroup)) {
            return true;
        }
        return false;
    }
}
