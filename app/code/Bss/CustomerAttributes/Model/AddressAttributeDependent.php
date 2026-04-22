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
namespace Bss\CustomerAttributes\Model;

use Bss\CustomerAttributes\Api\Data\AddressAttributeInterface;
use Magento\Framework\Model\AbstractModel;

class AddressAttributeDependent extends AbstractModel implements AddressAttributeInterface
{
    public const ID = 'id';
    public const ATTR_ID = 'attr_id';
    public const DEPENDENT_DATA = 'dependents_data';

    /**
     * Construct function
     */
    public function _construct()
    {
        $this->_init(ResourceModel\AddressAttribute\AddressAttributeDependent::class);
    }
    /**
     * Get Id
     *
     * @return array|mixed|null
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set Id
     *
     * @param mixed|int $id
     * @return \Bss\CustomerAttributes\Model\AddressAttributeDependent
     */
    public function setId($id)
    {
        $this->setData(self::ID, $id);
        return $this;
    }

    /**
     * Get Attribute Id
     *
     * @return array|mixed|null
     */
    public function getAttrId()
    {
        return $this->getData(self::ATTR_ID);
    }

    /**
     * Set Attribute Id
     *
     * @param mixed|int $attrId
     * @return \Bss\CustomerAttributes\Model\AddressAttributeDependent
     */
    public function setAttrId($attrId)
    {
        $this->setData(self::ATTR_ID, $attrId);
        return $this;
    }

    /**
     * Get Dependent Data
     *
     * @return array|mixed|null
     */
    public function getDependentsData()
    {
        return $this->getData(self::DEPENDENT_DATA);
    }

    /**
     * Get Dependent Data
     *
     * @param mixed $dependentData
     * @return \Bss\CustomerAttributes\Model\AddressAttributeDependent
     */
    public function setDependentsData($dependentData)
    {
        $this->setData(self::DEPENDENT_DATA, $dependentData);
        return $this;
    }
}
