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
namespace Bss\CatalogPermission\Plugin\Model;

use Bss\CatalogPermission\Helper\Data;
use Bss\CatalogPermission\Helper\ModuleConfig;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Bss\CatalogPermission\Model\ResourceModel\Category as ResourceCategory;
use Magento\Catalog\Model\CategoryFactory;

/**
 * Class Category
 *
 * @package Bss\CatalogPermission\Plugin\Model
 */
class Category extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var \Bss\CatalogPermission\Helper\ModuleConfig
     */
    protected $moduleConfig;
    /**
     * @var \Bss\CatalogPermission\Helper\Data
     */
    protected $helperData;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ResourceCategory
     */
    protected $bssCategoryResource;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Bss\CatalogPermission\Model\ResourceModel\Category
     */
    private $categoryResource;

    /**
     * Category constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param ModuleConfig $moduleConfig
     * @param Data $helperData
     * @param ResourceCategory $bssCategoryResource
     * @param CategoryFactory $categoryFactory
     * @param ResourceCategory $categoryResource
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        ModuleConfig $moduleConfig,
        Data $helperData,
        ResourceCategory $bssCategoryResource,
        CategoryFactory $categoryFactory,
        \Bss\CatalogPermission\Model\ResourceModel\Category $categoryResource,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->moduleConfig = $moduleConfig;
        $this->helperData = $helperData;
        $this->storeManager = $context->getStoreManager();
        $this->bssCategoryResource = $bssCategoryResource;
        $this->categoryFactory = $categoryFactory;
        $this->categoryResource = $categoryResource;
        parent::__construct($context, $data);
    }

    /**
     * Plugin after get active
     *
     * @param \Magento\Catalog\Model\Category $subject
     * @param bool $result
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function afterGetIsActive($subject, $result)
    {
        $parentId = $this->bssCategoryResource->getFirstParentCategorySetPermission($subject->getId());
        $parentCategory = $this->categoryFactory->create()->load($parentId);
        $enableCatalogPermission = $this->moduleConfig->enableCatalogPermission();
        $customerGroupId = $this->customerSession->getCustomerGroupId();
        $currentStoreId = $this->storeManager->getStore()->getId();
        $listIdSubCategory = $this->helperData->getIdCategoryByCustomerGroupId(
            $customerGroupId,
            $currentStoreId,
            false
        );
        $useParentCategory = $this->moduleConfig->useParentCategory();
        $data = $subject->getData();
        if ($useParentCategory && $parentId != null) {
            $data[$this->categoryResource->getColumnNameId()] = $parentCategory->getId();
            $data['bss_customer_group'] = $parentCategory->getData('bss_customer_group');
        }

        if (in_array($data[$this->categoryResource->getColumnNameId()], $listIdSubCategory) && $enableCatalogPermission) {
            return false;
        }

        if (isset($data['bss_customer_group']) && $enableCatalogPermission) {
            if ($data['bss_customer_group'] !== null) {
                if ($data['bss_customer_group'] == $customerGroupId || (is_array($data['bss_customer_group'])
                        && in_array($customerGroupId, $data['bss_customer_group']))) {
                    $result = false;
                }
            }
        }
        return $result;
    }
}
