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
 * @package    Bss_QuantityDropdown
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CatalogPermission\Plugin;

class CatalogWidgetCollectionPlugin
{
    /**
     * @var \Magento\Widget\Helper\Conditions
     */
    protected $conditionsHelper;

    /**
     * @var \Bss\CatalogPermission\Helper\ModuleConfig
     */
    protected $moduleConfig;

    /**
     * @var \Bss\CatalogPermission\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * CatalogWidgetCollectionPlugin constructor.
     * @param \Magento\Widget\Helper\Conditions $conditionsHelper
     * @param \Bss\CatalogPermission\Helper\ModuleConfig $moduleConfig
     * @param \Bss\CatalogPermission\Helper\Data $helperData
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Widget\Helper\Conditions $conditionsHelper,
        \Bss\CatalogPermission\Helper\ModuleConfig $moduleConfig,
        \Bss\CatalogPermission\Helper\Data $helperData,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->conditionsHelper = $conditionsHelper;
        $this->moduleConfig = $moduleConfig;
        $this->helperData = $helperData;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\CatalogWidget\Block\Product\ProductsList $subject
     * @param $result
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCreateCollection(
        \Magento\CatalogWidget\Block\Product\ProductsList $subject,
        $result
    ) {
        $enableCatalogPermission = $this->moduleConfig->enableCatalogPermission();
        if ($enableCatalogPermission) {
            $customerGroupId = $this->customerSession->getCustomerGroupId();
            $currentStoreId = $this->storeManager->getStore()->getId();
            $listBannedId = array_unique(
                $this->helperData
                    ->getIdCategoryByCustomerGroupId($customerGroupId, $currentStoreId, false)
            );
            if (empty($listBannedId)) {
                return $result;
            }
            $result = $this->setPermission($subject, $result, $listBannedId);
        }
        return $result;
    }

    /**
     * @param $subject
     * @param $result
     * @param $listBannedId
     * @return mixed
     */
    protected function setPermission($subject, $result, $listBannedId)
    {
        $isProductPermission = $this->moduleConfig->enableProductRestricted();
        if ($isProductPermission && $isProductPermission == 1) {
            $result->addCategoriesFilter(['nin' => $listBannedId]);
        } else {
            $conditionCategory = false;
            $conditions = $this->getCondition($subject);
            if (empty($conditions)) {
                return $result;
            }
            foreach ($conditions as $condition) {
                if (isset($condition['attribute']) &&
                    $condition['attribute'] == 'category_ids' &&
                    $condition['type'] == \Magento\CatalogWidget\Model\Rule\Condition\Product::class
                ) {
                    $conditionCategory = true;
                    break;
                }
            }
            if ($conditionCategory) {
                $result->addCategoriesFilter(['nin' => $listBannedId]);
            }
        }
        return $result;
    }

    /**
     * @param $subject
     * @return array
     */
    protected function getCondition($subject)
    {
        $conditions = $subject->getData('conditions_encoded')
            ? $subject->getData('conditions_encoded')
            : $subject->getData('conditions');
        if ($conditions) {
            $conditions = $this->conditionsHelper->decode($conditions);
        }
        return $conditions;
    }
}
