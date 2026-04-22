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
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomerAttributes\Model\ResourceModel\Option;

use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;

class Collection
{
    /**
     * @var AttributeOptionInterfaceFactory
     */
    protected $optionFactory;

    /**
     * @var CollectionFactory
     */
    protected $attributeOptionCollectionFactory;

    /**
     * @param AttributeOptionInterfaceFactory $optionFactory
     * @param CollectionFactory $attributeOptionCollectionFactory
     */
    public function __construct(
        AttributeOptionInterfaceFactory                      $optionFactory,
        CollectionFactory $attributeOptionCollectionFactory
    ) {
        $this->optionFactory = $optionFactory;
        $this->attributeOptionCollectionFactory = $attributeOptionCollectionFactory;
    }

    /**
     * Get Option Value By Id
     *
     * @param int $optionValueID
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection
     */
    public function getOptionValueById($optionValueID)
    {
        $optionFactory = $this->optionFactory->create();
        $optionFactory->load($optionValueID);
        $attributeId = $optionFactory->getAttributeId();
        $optionData = $this->attributeOptionCollectionFactory->create()
            ->setPositionOrder('asc')
            ->setAttributeFilter($attributeId)
            ->setIdFilter($optionValueID)
            ->setStoreFilter()
            ->load();
        return $optionData;
    }
}
