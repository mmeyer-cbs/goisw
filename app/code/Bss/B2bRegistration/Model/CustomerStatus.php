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
 * @package    Bss_B2bRegistration
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\B2bRegistration\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class CustomerStatus
 *
 * @package Bss\B2bRegistration\Model
 */
class CustomerStatus extends AbstractModel
{
    /**
     * { @inheritdoc }
     */
    public function _construct()
    {
        $this->_init(\Bss\B2bRegistration\Model\ResourceModel\CustomerStatus::class);
    }
}
