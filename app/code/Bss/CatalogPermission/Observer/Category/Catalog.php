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
namespace Bss\CatalogPermission\Observer\Category;

use Bss\CatalogPermission\Helper\Data;
use Bss\CatalogPermission\Helper\ModuleConfig;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Catalog
 *
 * @package Bss\CatalogPermission\Observer\Category
 */
class Catalog implements ObserverInterface
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
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * Catalog constructor.
     * @param \Bss\CatalogPermission\Helper\ModuleConfig $moduleConfig
     * @param \Magento\Framework\App\Response\Http $response
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Bss\CatalogPermission\Helper\Data $helperData
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     */
    public function __construct(
        ModuleConfig $moduleConfig,
        Http $response,
        StoreManagerInterface $storeManager,
        CategoryRepositoryInterface $categoryRepository,
        Data $helperData,
        \Magento\Framework\App\Response\RedirectInterface $redirect
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->response = $response;
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->helperData = $helperData;
        $this->redirect = $redirect;
    }

    /**
     * Observer execute
     *
     * @param Observer $observer
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {
        $enableCatalogPermission = $this->moduleConfig->enableCatalogPermission();
        if (!$enableCatalogPermission) {
            return $this;
        }
        $categoryId = $observer->getRequest()->getParams();
        if (isset($categoryId['id'])) {
            $categoryId = $categoryId['id'];
            $currentStoreId = $this->storeManager->getStore()->getId();
            $data = $this->categoryRepository->get($categoryId, $currentStoreId);
            $customerGroupId = $this->helperData->getCustomerGroupId();
            $listIdSubCategory = $this->helperData->getIdCategoryByCustomerGroupId(
                $customerGroupId,
                $currentStoreId,
                false
            );
            $pageRedirect = $customerUrl = $errorMessage = null;
            $referentUrl = $this->redirect->getRefererUrl();
            if (isset($data['bss_redirect_type']) && $data['bss_redirect_type'] == 2) {
                $pageRedirect = $data['bss_select_page'];
                $customerUrl = $data['bss_custom_url'];
                $errorMessage = $data['bss_error_message'];
            }
            $useParentCategory = $this->moduleConfig->useParentCategory();
            if ($useParentCategory && in_array($categoryId, $listIdSubCategory)) {
                $baseUrl = $this->storeManager->getStore()->getBaseUrl();
                $this->response->setRedirect(
                    $baseUrl .
                    'catalogpermission/index/index?pagetype=category&pageid=' . $pageRedirect .
                    '&customurl=' . $customerUrl .
                    '&referent=' . $referentUrl .
                    '&message=' . $errorMessage
                );
            }

            if (isset($data['bss_customer_group'])) {
                if ($data['bss_customer_group'] !== null) {
                    if ($data['bss_customer_group'] == $customerGroupId || (is_array($data['bss_customer_group'])
                            && in_array($customerGroupId, $data['bss_customer_group']))) {
                        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
                        $this->response->setRedirect(
                            $baseUrl .
                            'catalogpermission/index/index?pagetype=category&pageid=' . $pageRedirect .
                            '&customurl=' . $customerUrl .
                            '&referent=' . $referentUrl .
                            '&message=' . $errorMessage
                        );
                    }
                }
            }
        }
        return $this;
    }
}
