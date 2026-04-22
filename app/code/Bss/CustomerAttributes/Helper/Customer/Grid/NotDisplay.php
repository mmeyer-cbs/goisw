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
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Helper\Customer\Grid;

use Bss\CustomerAttributes\Helper\Data;
use Magento\Customer\Model\AttributeFactory;
use Magento\Eav\Model\EntityFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\App\Helper\Context;

/**
 * Class Data
 *
 * @package Bss\CustomerAttributes\Helper\Customer\Grid
 */
class NotDisplay extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     *
     */
    const CUSTOMER_ADDRESS = 'customer_address';

    /**
     * @var \Magento\Eav\Model\ResourceModel\Config
     */
    protected $entityTypeResource;

    /**
     * @var \Magento\Eav\Model\ConfigFactory
     */
    protected $entityType;

    /**
     * @var AttributeCollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * Attribute factory
     *
     * @var \Magento\Customer\Model\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var \Magento\Eav\Model\EntityFactory
     */
    protected $customerEntityFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * NotDisplay constructor.
     *
     * @param \Magento\Eav\Model\ResourceModel\Entity\Type $entityTypeResource
     * @param \Magento\Eav\Model\Entity\TypeFactory $entityType
     * @param ProductMetadataInterface $productMetadata
     * @param AttributeCollectionFactory $attributeCollectionFactory
     * @param AttributeFactory $attributeFactory
     * @param EntityFactory $customerEntityFactory
     * @param Context $context
     */
    public function __construct(
        Data $helperData,
        \Magento\Eav\Model\ResourceModel\Entity\Type $entityTypeResource,
        \Magento\Eav\Model\Entity\TypeFactory $entityType,
        AttributeCollectionFactory $attributeCollectionFactory,
        AttributeFactory $attributeFactory,
        EntityFactory $customerEntityFactory,
        Context $context
    ) {
        $this->helperData=$helperData;
        $this->entityTypeResource = $entityTypeResource;
        $this->entityType = $entityType;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->attributeFactory = $attributeFactory;
        $this->customerEntityFactory = $customerEntityFactory;
        parent::__construct($context);
    }

    /**
     * Not display type date in customer grid when version magento >= 2.4.0
     */
    public function updateDisplayCustomerGrid()
    {
        if ($this->checkVersionMagento() >= "2.4.0") {
            $attributeCollection = $this->getAllAttributesCollection();
            foreach ($attributeCollection as $attribute) {
                $attributeBackendType = $attribute->getBackendType();
                if ((($attributeBackendType == "datetime") || ($attributeBackendType == "date"))) {
                    $attribute->setIsUsedInGrid(false);
                    $attribute->save();
                }
            }

            $addressCollection = $this->getAllAddressCollection();
            foreach ($addressCollection as $attribute) {
                $attributeBackendType = $attribute->getBackendType();
                if ((($attributeBackendType == "datetime") || ($attributeBackendType == "date"))) {
                    $attribute->setIsUsedInGrid(false);
                    $attribute->save();
                }
            }
        }
    }

    /**
     * Get all attributes attribute customer by module Bss_CustomerAttributes create
     *
     * @return \Magento\Framework\Data\Collection\AbstractDb|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection|array
     */
    public function getAllAttributesCollection()
    {
        if ($this->checkEntityType(\Magento\Customer\Model\Customer::ENTITY)) {
            $entityTypeId = $this->customerEntityFactory->create()
                ->setType(\Magento\Customer\Model\Customer::ENTITY)
                ->getTypeId();
            $attribute = $this->attributeFactory->create()
                ->setEntityTypeId($entityTypeId);
            return $attribute->getCollection()
                ->addFieldToFilter('attribute_code', ['like' =>  'ca_%'])
                ->setOrder('sort_order', 'ASC');
        }
        return [];
    }
    /**
     * Get Attribute By Code
     *
     * @param $attributecode
     * @return array|\Magento\Framework\Data\Collection\AbstractDb|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getAttributeByCode($attributeCode)
    {
        if ($this->checkEntityType(\Magento\Customer\Model\Customer::ENTITY)) {
            $entityTypeId = $this->customerEntityFactory->create()
                ->setType(\Magento\Customer\Model\Customer::ENTITY)
                ->getTypeId();
            $attribute = $this->attributeFactory->create()
                ->setEntityTypeId($entityTypeId);
            return $attribute->getCollection()
                ->addFieldToFilter('attribute_code', ['eq' =>  $attributeCode])
                ->setOrder('sort_order', 'ASC');
        }
        return [];
    }
    /**
     * Get all attributes address by module Bss_CustomerAttributes create
     *
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection|array
     */
    public function getAddressAttributeByCode($addressAttributeCode)
    {
        if ($this->checkEntityType(self::CUSTOMER_ADDRESS)) {
            $entityTypeId = $this->customerEntityFactory->create()
                ->setType(self::CUSTOMER_ADDRESS)
                ->getTypeId();
            $collection = $this->attributeCollectionFactory->create();
            return $collection->setEntityTypeFilter($entityTypeId)
                ->addFieldToFilter('attribute_code', ['eq' =>  $addressAttributeCode])
                ->setOrder('sort_order', 'ASC');
        }
        return [];
    }

    /**
     * Get all attributes address by module Bss_CustomerAttributes create
     *
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection|array
     */
    public function getAllAddressCollection()
    {
        if ($this->checkEntityType(self::CUSTOMER_ADDRESS)) {
            $entityTypeId = $this->customerEntityFactory->create()
                ->setType(self::CUSTOMER_ADDRESS)
                ->getTypeId();
            $collection = $this->attributeCollectionFactory->create();
            return $collection->setEntityTypeFilter($entityTypeId)
                ->addFieldToFilter('attribute_code', ['like' =>  'ad_%'])
                ->setOrder('sort_order', 'ASC');
        }
        return [];
    }

    /**
     * Check version magento
     *
     * @return string
     */
    public function checkVersionMagento()
    {
        return $this->helperData->checkVersionMagento();
    }

    /**
     * Check entity type
     *
     * @param string $entityTypeCode
     * @return bool
     */
    public function checkEntityType(string $entityTypeCode)
    {
        $entityTypeFactory = $this->entityType->create();
        $this->entityTypeResource->load($entityTypeFactory, $entityTypeCode, "entity_type_code");
        if ($entityTypeFactory->getEntityTypeId()) {
            return true;
        }
        return false;
    }
}
