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

namespace Bss\CustomPricing\Model\ResourceModel\PriceRule;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Bss\CustomPricing\Model\ResourceModel\PriceRule as ResourceModel;
use Bss\CustomPricing\Model\PriceRule as Model;

/**
 * Class Price Rule Collection
 */
class Collection extends AbstractCollection
{
    protected $_idFieldName = Model::ID;

    /**
     * Relation fields
     *
     * @var array
     */
    protected $with = [];

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

    /**
     * Set relation fields
     *
     * @param array|string $fields
     * @return $this
     */
    public function with($fields)
    {
        if (!is_array($fields)) {
            $fields = [$fields];
        }
        foreach ($fields as $field) {
            if (!in_array($field, $this->with)) {
                array_push($this->with, $field);
            }
        }
        return $this;
    }

    /**
     * Eager loading relations data
     *
     * @param \Magento\Framework\DataObject $item
     * @return \Magento\Framework\DataObject|void
     */
    protected function beforeAddLoadedItem(\Magento\Framework\DataObject $item)
    {
        /** @var Model $object */
        $object = parent::beforeAddLoadedItem($item);
        if (method_exists($object, "with") &&
            method_exists($object, "reloadRelations")
        ) {
            $object->with($this->with)->reloadRelations($object);
        }
        return $object;
    }
}
