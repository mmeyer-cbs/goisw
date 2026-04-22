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
declare(strict_types=1);
namespace Bss\CustomerAttributes\Model\ResourceModel\AddressAttribute;

class AddressAttributeDependent extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Create table bss_address_attribute_dependent with id field
     */
    public function _construct()
    {
        $this->_init("bss_address_attribute_dependent", "id");
    }
}
