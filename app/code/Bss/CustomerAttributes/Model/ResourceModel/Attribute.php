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
namespace Bss\CustomerAttributes\Model\ResourceModel;

use Magento\Eav\Model\Entity\Attribute as EntityAttribute;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * EAV attribute resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Attribute extends \Magento\Customer\Model\ResourceModel\Attribute
{
    /**
     * @var string
     */
    protected $_idFieldName = "main_table.attribute_id";
    /**
     * @return string
     */
    public function getIdFieldName()
    {
        return 'main_table.attribute_id';
    }
}
