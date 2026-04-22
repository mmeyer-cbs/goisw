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
 * @package    Bss_OrderAmount
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OrderAmount\Model;

use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\TypeListInterface;

/**
 * Class SetOrderAmount
 *
 * @package Bss\OrderAmount\Model
 */
class SetOrderAmount implements \Bss\OrderAmount\Api\OrderAmountInterface
{
    /**
     * @var \Bss\OrderAmount\Model\Config\Backend\Serialized
     */
    protected $serialized;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $write;

    /**
     * @var \Bss\OrderAmount\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @var Pool
     */
    protected $cacheFrontendPool;

    /**
     * CreateOrderAmount constructor.
     * @param \Bss\OrderAmount\Helper\Data $helper
     * @param \Bss\OrderAmount\Model\Config\Backend\Serialized $serialized
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $write
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param TypeListInterface $cacheTypeList
     * @param Pool $cacheFrontendPool
     */
    public function __construct(
        \Bss\OrderAmount\Helper\Data $helper,
        \Bss\OrderAmount\Model\Config\Backend\Serialized $serialized,
        \Magento\Framework\App\Config\Storage\WriterInterface $write,
        \Magento\Framework\Math\Random $mathRandom,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool
    ) {
        $this->serialized = $serialized;
        $this->write = $write;
        $this->helper = $helper;
        $this->mathRandom = $mathRandom;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
    }

    /**
     * Set new or update Order Amount
     *
     * @param mixed $data
     * @return array|bool|float|int|string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function setOrderAmount($data)
    {
        $oldValues = $this->helper->getAmountData();
        if (is_array($oldValues)) {
            $newCustomerGroup = $this->getCustomerGroups($data);
            $newValues = $this->changedValues($oldValues, $newCustomerGroup, $data);
            $values = $this->setKeyUnique($newValues);
        } else {
            $values = $this->setKeyUnique($data);
        }
        $this->serialized->setValue($values);
        $this->serialized->beforeSave();
        $values = $this->serialized->getValue();
        $this->write->save('sales/minimum_order/amount', $values);
        $this->flushCache();
        return $this->serialized->serializer->unserialize($values);
    }

    /**
     * Set key for item
     *
     * @param array $arr
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setKeyUnique ($arr) {
        $i = 0;
        $result = [];
        foreach ($arr as $item) {
            ++$i;
            $key = '_'.$this->getRandomNumber(10000000, 99999999).'_'.$i;
            $result[$key] = $item;
        }
        return $result;
    }

    /**
     * Get random key
     *
     * @param int $min
     * @param null|int $max
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRandomNumber($min = 0, $max = null)
    {
        return $this->mathRandom->getRandomNumber($min, $max);
    }

    /**
     * Get customer groups in new values
     *
     * @param array $values
     * @return array
     */
    public function getCustomerGroups($values)
    {
        $result = [];
        foreach ($values as $value) {
            $result[] = $value['customer_group'];
        }
        return $result;
    }

    /**
     * Check isset order amount for customer group updated
     *
     * @param array $oldValues
     * @param array $newGroup
     * @param array $newValues
     * @return mixed
     */
    public function changedValues($oldValues, $newGroup, $newValues)
    {
        $notChangedValues = [];
        foreach ($oldValues as $value) {
            if (!in_array($value['customer_group'], $newGroup)) {
                $notChangedValues = $value;
            }
        }
        if (!empty($notChangedValues)) {
            $newValues[] = $notChangedValues;
            return $newValues;
        }
        return $newValues;
    }

    /**
     * Flush cache after save
     */
    public function flushCache()
    {
        $_types = [
            'config',
            'full_page'
        ];

        foreach ($_types as $type) {
            $this->cacheTypeList->cleanType($type);
        }
        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }
}
