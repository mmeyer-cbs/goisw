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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomerAttributes\Plugin\Model\Entity;

use Bss\CustomerAttributes\Helper\Customerattribute;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class Type
{
    /**
     * @var Customerattribute
     */
    private $customerAttribute;

    /**
     * Entity type constructor.
     * @param Customerattribute $customerattribute
     */
    public function __construct(
        Customerattribute $customerattribute
    ) {
        $this->customerAttribute = $customerattribute;
    }

    public function afterGetAttributeCollection(\Magento\Eav\Model\Entity\Type $subject, $result)
    {
        $attributes = $this->customerAttribute->getAddressCollection();
        $disableAttributeCodes = [''];
        /* @var AbstractAttribute $attribute */
        foreach ($attributes as $attribute) {
            if ($attribute->getIsUserDefined()) {
                if (!$attribute->getIsVisible() || !$this->customerAttribute->isEnable()) {
                    $disableAttributeCodes[] = $attribute->getAttributeCode();
                }
            }
        }
        return $result->addFieldToFilter('attribute_code', ['nin' => $disableAttributeCodes]);
    }
}
