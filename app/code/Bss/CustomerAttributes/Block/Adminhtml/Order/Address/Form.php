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
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomerAttributes\Block\Adminhtml\Order\Address;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Add Address Info In Order Detail
 *
 */
class Form extends \Magento\Sales\Block\Adminhtml\Order\Address\Form
{
    /**
     * Return Custom Address values
     *
     * @return array
     */
    public function getCustomAddressValues()
    {
        return $this->_getAddress()->getCustomerAddressAttribute();
    }

    /**
     * @param \Magento\Customer\Api\Data\AttributeMetadataInterface[] $attributes
     * @param \Magento\Framework\Data\Form\AbstractForm $form
     * @return $this|Form
     */
    protected function _addAttributesToForm($attributes, \Magento\Framework\Data\Form\AbstractForm $form)
    {
        parent::_addAttributesToForm($attributes, $form);

        foreach ($attributes as $attribute) {
            $inputType = $attribute->getFrontendInput();
            if ($inputType && $inputType == 'date') {
                $format = \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT;
                /** @var AbstractElement $element */
                foreach ($form->getElements() as $element) {
                    if ($element->getId() === $attribute->getAttributeCode()) {
                        $element->setDateFormat($format);
                    }
                }
            }
        }
        return $this;
    }
}
