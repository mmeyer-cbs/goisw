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
 * @package    Bss_B2bRegistration
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\B2bRegistration\Plugin;

use Bss\B2bRegistration\Helper\Data;

class Form
{
    /**#@+
     * Values for ignoreInvisible parameter in constructor
     */
    const IGNORE_INVISIBLE = true;

    /**
     * @var bool
     */
    protected $_ignoreInvisible = true;

    /**
     * @var array
     */
    protected $_filterAttributes;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Form constructor.
     * @param Data $helper
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        Data $helper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->helper = $helper;
        $this->request = $request;
    }

    /**
     * @param \Magento\Customer\Model\Metadata\Form $subject
     * @param callable $proceed
     * @return \Magento\Customer\Api\Data\AttributeMetadataInterface[]
     */
    public function aroundGetAllowedAttributes(
        \Magento\Customer\Model\Metadata\Form $subject,
        callable $proceed
    ) {
        $enable = $this->helper->isEnableDateField();
        $enableDob = $this->helper->isEnableDateField();
        $enableDobDefault = $this->helper->getDobFieldDefault();
        $controllerName = $this->request->getFullActionName();
        if ($enable && $enableDob && !$enableDobDefault && $controllerName == "btwob_account_createpost") {
            $attributes = $subject->getAttributes();
            if (!$this->_ignoreInvisible) {
                $this->_ignoreInvisible = self::IGNORE_INVISIBLE;
            }
            if (!$this->_filterAttributes) {
                $this->_filterAttributes = [];
            }

            $attributes = $this->checkAttribute($attributes);

            return $attributes;
        } else {
            return $proceed();
        }
    }

    /**
     * @param array $attributes
     * @return mixed
     */
    protected function checkAttribute($attributes)
    {
        foreach ($attributes as $attributeCode => $attribute) {
            if ($attributeCode == "dob") {
                continue;
            }
            if ($this->_ignoreInvisible && !$attribute->isVisible() ||
                in_array($attribute->getAttributeCode(), $this->_filterAttributes)
            ) {
                unset($attributes[$attributeCode]);
            }
        }

        return $attributes;
    }
}
