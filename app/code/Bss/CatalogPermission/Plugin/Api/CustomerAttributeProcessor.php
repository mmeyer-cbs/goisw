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
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CatalogPermission\Plugin\Api;

/**
 * Class CustomerAttributeProcessor
 */
class CustomerAttributeProcessor
{
    /**
     * Set bss customer group follow format ["x","y"]
     *
     * @param \Magento\Catalog\Model\CategoryRepository $subject
     * @param \Magento\Catalog\Api\Data\CategoryInterface $category
     * @return array
     */
    public function beforeSave(
        \Magento\Catalog\Model\CategoryRepository $subject,
        \Magento\Catalog\Api\Data\CategoryInterface $category
    ) {
        if ($this->checkCustomerGroupAttribute($category) && isset($category['id'])) {
            $savePermission = $category['custom_attributes']['bss_customer_group'];
            if ($savePermission->getValue()) {
                $customerGroup = explode(',', $savePermission->getValue());
                $savePermission->setValue($customerGroup);
            }
        }
        return [$category];
    }

    /**
     * Check input bss customer group
     *
     * @param \Magento\Catalog\Api\Data\CategoryInterface $category
     * @return bool
     */
    public function checkCustomerGroupAttribute($category)
    {
        $inputData = $category->getCustomAttributes();
        foreach ($inputData as $item) {
            if ($item->getAttributeCode() == 'bss_customer_group') {
                return true;
            }
        }
        return false;
    }
}
