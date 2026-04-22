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
declare(strict_types=1);

namespace Bss\CatalogPermission\Plugin\Block\Product;

use Bss\CatalogPermission\Helper\ModuleConfig;
use Bss\CatalogPermission\Model\Category;
use Magento\Framework\Exception\NoSuchEntityException;

class ListProduct
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryFactory;

    /**
     * @var Category
     */
    private $categoryChecker;

    /**
     * @var ModuleConfig
     */
    private $helper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collecionFactory
     * @param Category $categoryChecker
     * @param ModuleConfig $helper
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Customer\Model\Session                                 $customerSession,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collecionFactory,
        Category                                                        $categoryChecker,
        ModuleConfig                                                    $helper,
        \Magento\Framework\App\RequestInterface                         $request
    ) {
        $this->customerSession = $customerSession;
        $this->categoryFactory = $collecionFactory;
        $this->categoryChecker = $categoryChecker;
        $this->helper = $helper;
        $this->request = $request;
    }

    /**
     * Get Search Product
     *
     * @param \Magento\Catalog\Block\Product\ListProduct $subject
     * @param \Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection $result
     * @return \Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection
     * @throws NoSuchEntityException|\Magento\Framework\Exception\LocalizedException
     */
    public function afterGetLoadedProductCollection($subject, $result)
    {
        if ($this->helper->enableCatalogPermission() && $this->helper->enableProductRestricted()) {
            $action = $this->request->getFullActionName();
            if ($this->isSearchPage($action)) {
                if ($result->count() > 0) {
                    $customerGroupId = $this->customerSession->getCustomerGroupId();
                    $bannedCatIds = array_unique($this->categoryChecker->getListIdCategoryByCustomerGroupId(
                        $customerGroupId,
                        $this->categoryChecker->getCurrentStore()->getStore()->getId()
                    ));
                    /** @var \Magento\Catalog\Model\Product $item */
                    foreach ($result->getItems() as $item) {
                        $catIds = $item->getCategoryIds();
                        if ($this->checkCategoryId($bannedCatIds, $catIds)) {
                            $result->removeItemByKey($item->getId());
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Check search page
     *
     * @param string $actionName
     * @return bool
     */
    public function isSearchPage($actionName)
    {
        return $actionName == 'catalogsearch_result_index'
            || $actionName == 'catalogsearch_advanced_result';
    }

    /**
     * Check cate ids of product in banned cate ids
     *
     * @param array $bannedCateIds
     * @param array $cateIds
     * @return bool
     */
    public function checkCategoryId($bannedCateIds, $cateIds)
    {
        foreach ($bannedCateIds as $id) {
            if (in_array($id, $cateIds)) {
                return true;
            }
        }
        return false;
    }
}
