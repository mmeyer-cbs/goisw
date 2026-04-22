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
 * @copyright  Copyright (c) 2015-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\OrderAmount\Model\Config\Backend;

use \Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Serialized
 *
 * @package Bss\OrderAmount\Model\Config\Backend
 */
class Serialized extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    public $serializer;

    /**
     * Serialized constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->serializer = $serializer;
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @return \Magento\Framework\App\Config\Value|void
     */
    protected function _afterLoad()
    {
        if (!is_array($this->getValue())) {
            $value = $this->getValue();
            $this->setValue(empty($value) ? false : $this->serializer->unserialize($value));
        }
    }

    /**
     * @return \Magento\Framework\App\Config\Value
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function beforeSave()
    {
        $values = $this->getValue();
        if (is_array($values)) {
            unset($values['__empty']);
            $arrCustomerGroup = [];
            foreach ($values as $value) {
                if (is_array($value)) {
                    $arrCustomerGroup[] = $value['customer_group'];
                    $miniumAmount = $value['minimum_amount'];
                    if (!is_numeric($miniumAmount)) {
                        throw new \Magento\Framework\Exception\ValidatorException(__(
                            'Minimum Amount is not a number.'
                        ));
                    } elseif ($miniumAmount < 0) {
                        throw new \Magento\Framework\Exception\ValidatorException(__(
                            'Minimum Amount must be greater than zero'
                        ));
                    }
                }
            }
            $this->checkUniqueCustomerGroup($arrCustomerGroup);
        }
        $this->setValue($values);

        if (is_array($this->getValue())) {
            $this->setValue($this->serializer->serialize($this->getValue()));
        }
        return parent::beforeSave();
    }

    /**
     * @param array $arrCustomerGroup
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    protected function checkUniqueCustomerGroup($arrCustomerGroup)
    {
        $uniqueCustomerGroup = array_unique($arrCustomerGroup);
        if ($uniqueCustomerGroup != $arrCustomerGroup) {
            throw new \Magento\Framework\Exception\ValidatorException(__(
                'Duplicate Customer Group.'
            ));
        }
    }
}
