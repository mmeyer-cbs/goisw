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
 * @copyright  Copyright (c) 2022-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CatalogPermission\Model;

class GetProductIdQuery
{
    /**
     * @var Category
     */
    protected $category;

    /**
     * @var null
     */
    protected $ids;

    /**
     * @param Category $category
     */
    public function __construct(
        \Bss\CatalogPermission\Model\Category $category
    ) {
        $this->category = $category;
    }

    /**
     * Get product ids for must not condition
     *
     * @return array|bool|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Db_Statement_Exception
     */
    public function getProductIds()
    {
        if (!$this->ids) {
            $this->ids = $this->category->getProductIdsInCategoryBanned();
        }
        return $this->ids;
    }
}
