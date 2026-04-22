<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Bss\CustomPricing\Model\Rule\Condition\Customer;

/**
 * Price Rule Customer Condition data model
 *
 * @method string getAttribute() Returns attribute code
 */
class Customer extends AbstractCustomers
{
    /**
     * Retrieve Explicit Apply, Apply button on element value render.
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getExplicitApply()
    {
        switch ($this->getAttribute()) {
            case ('specified' || 'groups'):
                return true;
            default:
                break;
        }
        if (is_object($this->getAttributeObject())) {
            switch ($this->getAttributeObject()->getFrontendInput()) {
                case 'date':
                    return true;
                default:
                    break;
            }
        }
        return false;
    }

    /**
     * Validate groups and special attributes
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $attrCode = $this->getAttribute();
        switch ($attrCode) {
            case "groups":
                $validatedValue = $model->getData('group_id');
                $result = $this->validateAttribute($validatedValue);
                return (bool)$result;
            case "specified":
                $validatedValue = $model->getId();
                $result = $this->validateAttribute($validatedValue);
                return (bool)$result;
            default:
                return parent::validate($model);
        }
    }
}
