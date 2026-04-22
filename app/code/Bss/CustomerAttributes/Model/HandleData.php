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
declare(strict_types=1);
namespace Bss\CustomerAttributes\Model;

use Bss\CustomerAttributes\Api\Data\AddressAttributeInterface;
use Bss\CustomerAttributes\Helper\Data;

class HandleData
{
    /**
     * @var AttributeDependentRepository
     */
    protected $attributeDependentRepository;

    /**
     * @var SerializeData
     */
    protected $serializer;

    /**
     * @var Data
     */
    protected $display;

    /**
     * @var AddressAttributeDependentRepository
     */
    protected $addressAttributeDependentRepository;

    /**
     * @param AttributeDependentRepository $attributeDependentRepository
     * @param SerializeData $serializer
     * @param Data $display
     * @param AddressAttributeDependentRepository $addressAttributeDependentRepository
     */
    public function __construct(
        AttributeDependentRepository $attributeDependentRepository,
        SerializeData $serializer,
        Data $display,
        AddressAttributeDependentRepository $addressAttributeDependentRepository
    ) {
        $this->attributeDependentRepository = $attributeDependentRepository;
        $this->serializer = $serializer;
        $this->display = $display;
        $this->addressAttributeDependentRepository =$addressAttributeDependentRepository;
    }
    /**
     * Check module enable Customer Attribute Dependent
     *
     * @return mixed|string
     */
    public function isEnableCustomerAttributeDependency()
    {
        return $this->display->isEnableCustomerAttributeDependency();
    }

    /**
     * Display Child Values
     *
     * @return mixed|string
     */
    public function displayChildValues()
    {
        return $this->display->displayChildValues();
    }

    /**
     * Get Dependent Data
     *
     * @param mixed|string $attrCode
     * @return AttributeDependent
     */
    public function getDependentData($attrCode)
    {
        return $this->attributeDependentRepository->getDataByAttrID($attrCode);
    }

    /**
     * Get Address Dependent Data
     *
     * @param mixed|string $attrCode
     * @return AddressAttributeInterface
     */
    public function getAddressDependentData($attrCode)
    {
        return $this->addressAttributeDependentRepository->getDataByAttrID($attrCode);
    }

    /**
     * Get List Dependent AttributeCode
     *
     * @param mixed|array $attrData
     * @return array
     */
    public function getListDependentAttributeCode($attrData)
    {
        $almostDependentArr = [];
        $dependentArr = [];
        if ($attrData !== null) {
            foreach ($attrData as $valueCollection) {
                if ($valueCollection['entity_type_id'] == 2) {
                    $attrId = $this->getAddressDependentData($valueCollection['attribute_id'])->getData('dependents_data');
                } else {
                    $attrId = $this->getDependentData($valueCollection['attribute_id'])->getData('dependents_data');
                }
                $dependentData = $this->serializer->decodeFunction($attrId);
                if ($dependentData && count($dependentData) > 0) {
                    foreach ($dependentData as $dependentDataKeyValue) {
                        array_push(
                            $dependentArr,
                            $dependentDataKeyValue['attribute-values']['dependent_attribute']['value']
                        );
                        $almostDependentArr = (array_unique($dependentArr));
                    }
                }
            }
        }
        return $almostDependentArr;
    }

    /**
     * Get List DisableAttribute
     *
     * @param mixed|array $attrData
     * @return array
     */
    public function getListDisableAttribute($attrData)
    {
        $notAvailableAttribute = [];
        foreach ($attrData as $valueCollection) {
            if (!$valueCollection['is_visible']) {
                array_push($notAvailableAttribute, $valueCollection['attribute_code']);
            }
        }
        return $notAvailableAttribute;
    }

    /**
     * Get List AttributeId NotShow
     *
     * @param mixed|array $attrData
     * @return array
     */
    public function getListAttributeIdNotShow($attrData)
    {
        $finalDependentArr = [];
        $almostDependentArr = $this->getListDependentAttributeCode($attrData);
        foreach ($attrData as $valueCollection) {
            if (!$valueCollection['is_visible']) {
                array_push($finalDependentArr, $valueCollection['attribute_id']);
            }
            if ($this->isEnableCustomerAttributeDependency() && $almostDependentArr) {
                foreach ($almostDependentArr as $almostValue) {
                    if ($valueCollection['attribute_code'] == $almostValue) {
                        array_push($finalDependentArr, $valueCollection['attribute_id']);
                    }
                }
            }
        }
        return $finalDependentArr;
    }

    /**
     * Validate data dependent
     *
     * @param mixed|array $attributeValues
     * @param mixed|array $attributeCollectionData
     */
    public function validateDependentAttributeInfo($attributeValues, $attributeCollectionData)
    {
        $notAvailableAttribute = $this->getListDisableAttribute($attributeCollectionData);
        foreach ($attributeValues as $attrKey => $attrValue) {
            $dependentAttr = $this->serializer->decodeFunction($attrValue['value']['dependents_data']);
            foreach ($dependentAttr as $attrValueKey => $attrValueJr) {
                if (in_array(
                    $attrValueJr['attribute-values']['dependent_attribute']['value'],
                    $notAvailableAttribute
                )
                ) {
                    unset($dependentAttr[$attrValueKey]);
                    $attributeValues[$attrKey]['value']['dependents_data'] =
                        $this->serializer->encodeFunction($dependentAttr);
                }
            }
        }
        return $attributeValues;
    }

    /**
     * Validate date before saving in Backend
     *
     * @param array|mixed $arrDependentsData
     * @return bool
     */
    public function validateDependentDataBE($arrDependentsData)
    {
        if (isset($arrDependentsData)) {
            foreach ($arrDependentsData as $value) {
                foreach ($value as $v) {
                    if (empty($v['dependent_attribute']['value'])) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Get Dependent Data In BackEnd Before Save
     *
     * @param array|mixed $dependentsDataArray
     * @return array|mixed
     */
    public function getDependentDataBE($dependentsDataArray)
    {
        if (isset($dependentsDataArray)) {
            $arr = [];
            $firstKey = array_key_first($dependentsDataArray);
            $lastKey = array_key_last($dependentsDataArray);
            for ($i = $firstKey; $i <= $lastKey - 1; $i++) {
                {
                if (empty($dependentsDataArray[$i])) {
                    continue;
                }
                $firstData= $this->serializer->encodeFunction($dependentsDataArray[$i]);
                for ($j = $i + 1; $j <= $lastKey; $j++) {
                    if (empty($dependentsDataArray[$j])) {
                        continue;
                    }
                    $nextData = $this->serializer->encodeFunction($dependentsDataArray[$j]);
                    if (strcmp($firstData, $nextData) == 0) {
                        //Get duplicate key
                        array_push($arr, $j);
                    }
                }
                }
            }
            return array_diff_key($dependentsDataArray, array_flip(array_unique($arr)));
        } else {
            return $dependentsDataArray;
        }
    }

    /**
     * Save Dependent Data In BackEnd
     *
     * @param string|mixed $dependentsData
     * @param int $attributeId
     * @param array|mixed $model
     */
    public function saveDependentDataBE($dependentsData, $attributeId, $model)
    {
        if ($attributeId) {
            $update = $model->getDataByAttrID($attributeId);
            $updateData = $update->getData();
            if ($updateData) {
                $updateId = $update->getId();
                $update = $model->load($updateId);
                $update->setDependentsData($dependentsData);
            } else {
                $update->setAttrId($attributeId);
                $update->setDependentsData($dependentsData);
            }
            $update->save();
        }
    }

    /**
     * Get All Attribute Dependent Information in Be
     *
     * @param array|mixed $attributes
     * @return array
     */
    public function getAllAttributeDependentBe($attributes)
    {
        $attributeValues = [];
        /** @var \Magento\Eav\Model\Entity\Attribute $attr */
        foreach ($attributes as $attr) {
            if (in_array($attr->getFrontendInput(), ['boolean', 'checkboxs', 'select', 'radio', 'multiselect'])) {
                if ($attrOtps = $attr->getOptions()) {
                    foreach ($attrOtps as $option) {
                        $attributeValues[$attr->getAttributeCode()][] = [
                            'value' => $option->getValue(),
                            'label' => $option->getLabel()
                        ];
                    }
                }
            }
        }
        return $attributeValues;
    }

    /**
     * Validate All Attribute Dependent BE
     *
     * @param array|mixed $blockObj
     * @param int $customerAttributeId
     * @return mixed
     */
    public function validateAllAttributeDependentBe($blockObj, $customerAttributeId)
    {
        foreach ($blockObj as $key => $value) {
            if ($value['attribute_id'] == $customerAttributeId) {
                unset($blockObj[$key]);
            }
        }
        return $blockObj;
    }
}
