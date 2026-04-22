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
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomPricing\Model\ResourceModel\ProductPrice;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Bss\CustomPricing\Model\ResourceModel\ProductPrice as ResourceModel;
use Bss\CustomPricing\Model\ProductPrice as Model;

/**
 * Class Price Rule Collection
 */
class Collection extends AbstractCollection
{
    protected $_idFieldName = Model::ID;

    /**
     * Collection initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            Model::class,
            ResourceModel::class
        );
    }
}
