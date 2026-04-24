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
namespace Bss\CatalogPermission\Plugin;

use Magento\Framework\Message\ManagerInterface;
use Bss\CatalogPermission\Helper\ModuleConfig;
use \Magento\Store\Model\StoreRepository;

/**
 * Class PostDataProcessor
 *
 * @package Bss\CatalogPermission\Plugin
 */
class PostDataProcessor
{
    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var ModuleConfig
     */
    protected $bssHelper;

    /**
     * @var StoreRepository
     */
    protected $storeRepository;

    /**
     * @var \Bss\CatalogPermission\Helper\Data
     */
    protected $helper;

    /**
     * PostDataProcessor constructor.
     * @param ManagerInterface $messageManager
     * @param ModuleConfig $bssHelper
     * @param StoreRepository $storeRepository
     * @param \Bss\CatalogPermission\Helper\Data $helper
     */
    public function __construct(
        ManagerInterface $messageManager,
        ModuleConfig $bssHelper,
        StoreRepository $storeRepository,
        \Bss\CatalogPermission\Helper\Data $helper
    ) {
        $this->messageManager = $messageManager;
        $this->bssHelper = $bssHelper;
        $this->storeRepository = $storeRepository;
        $this->helper = $helper;
    }

    /**
     * Around Plugin
     *
     * @param \Magento\Cms\Controller\Adminhtml\Page\PostDataProcessor $subject
     * @param \Closure $proceed
     * @param array $data
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundFilter(
        \Magento\Cms\Controller\Adminhtml\Page\PostDataProcessor $subject,
        \Closure $proceed,
        $data
    ) {
        if (isset($data['bss_customer_group']) && ($data['bss_customer_group'])) {
            $data['bss_customer_group'] = $this->helper->returnJson()->serialize($data['bss_customer_group']);
        }
        $allStores = $this->getAllStores();
        $allStoreIds = array_keys($allStores);
        if ($this->validateSaveStoreview($data, $allStoreIds, $allStores)) {
            unset($data['store_id']);
        }
        if ($this->validateDisablePage($data, $allStoreIds, $allStores)) {
            unset($data['is_active']);
        }
        return $proceed($data);
    }

    /**
     * @param array $data
     * @param array $allStoreIds
     * @param array $allStores
     * @return bool
     */
    protected function validateSaveStoreview($data, $allStoreIds, $allStores)
    {
        $isPageToRedirect = false;
        if (!isset($data['store_id'])) {
            return $isPageToRedirect;
        }
        $unselectedStoreIds = array_diff($allStoreIds, $data['store_id']);
        if ($data['store_id'][0] != 0) {
            $storeName = "";
            foreach ($unselectedStoreIds as $storeId) {
                if ($this->bssHelper->getPageIdToRedirectCms($storeId) == $data['page_id'] ||
                    $this->bssHelper->getPageIdToRedirect($storeId) == $data['page_id']
                ) {
                    $isPageToRedirect = true;
                    $storeName = $allStores[$storeId];
                }
                break;
            }
            if ($isPageToRedirect) {
                $this->messageManager->addNoticeMessage(
                    __('Cannot change Store View. This page is used in Catalog Permission in store %1', $storeName)
                );
            }
        }
        return $isPageToRedirect;
    }

    /**
     * @param array $data
     * @param array $allStoreIds
     * @param array $allStores
     * @return bool
     */
    protected function validateDisablePage($data, $allStoreIds, $allStores)
    {
        $isPageToRedirect = false;
        $storeName = "";
        foreach ($allStoreIds as $storeId) {
            if ($this->bssHelper->getPageIdToRedirectCms($storeId) == $data['page_id'] ||
                $this->bssHelper->getPageIdToRedirect($storeId) == $data['page_id']
            ) {
                $isPageToRedirect = true;
                $storeName = $allStores[$storeId];
            }
            break;
        }
        if ($isPageToRedirect) {
            if ($data['is_active'] == 0) {
                $this->messageManager->addNoticeMessage(
                    __('Cannot disable page. This page is used in Catalog Permission in store %1', $storeName)
                );
            }
        }
        return $isPageToRedirect;
    }

    /**
     * @return array
     */
    protected function getAllStores()
    {
        $stores = $this->storeRepository->getList();
        $storeList = [];
        foreach ($stores as $store) {
            if ($store["store_id"] > 0) {
                $storeId = $store["store_id"];
                $storeName = $store["name"];
                $storeList[$storeId] = $storeName;
            }
        }
        return $storeList;
    }
}
