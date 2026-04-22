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

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Catalog\Model\CategoryRepository;

/**
 * Class InvalidCache
 *
 * @package Bss\CatalogPermission\Observer\Category
 */
class InvalidCache implements ObserverInterface
{
    /**
     * @var TypeListInterface
     */
    protected $typeList;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * InvalidCache constructor.
     * @param TypeListInterface $typeList
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(
        TypeListInterface $typeList,
        CategoryRepository $categoryRepository
    ) {
        $this->typeList = $typeList;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Observer execute
     *
     * @param Observer $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $requestCategory = $observer->getCategory();
        $requestClearCache = false;
        if ($requestCategory && $requestCategory->getId()) {
            $savedCategory = $this->categoryRepository->get($requestCategory->getId());
            $savedCustomerGroup = $this->convertCustomerGroup($savedCategory->getData('bss_customer_group'));
            $requestCustomerGroup = $this->convertCustomerGroup($requestCategory->getData('bss_customer_group'));
            if ($savedCustomerGroup != $requestCustomerGroup) {
                $requestClearCache = true;
            }
        } else {
            $requestClearCache = true;
        }
        if ($requestClearCache) {
            $this->typeList->invalidate(
                \Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER
            );
        }
        return $this;
    }

    /**
     * Convert array value customer group to string
     *
     * @param array|string $group
     * @return string
     */
    protected function convertCustomerGroup($group)
    {
        if (is_array($group)) {
            return implode(',', $group);
        }
        return $group;
    }
}
