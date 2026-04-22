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

namespace Bss\CustomerAttributes\Block\Form\AddressField;

use Bss\CustomerAttributes\Block\Adminhtml\Address\Edit\Tab\Attribute\AddressAttribute;
use Bss\CustomerAttributes\Controller\Adminhtml\AddressAttribute\Edit;
use Bss\CustomerAttributes\Helper\Customer\Grid\NotDisplay;
use Bss\CustomerAttributes\Helper\Data;
use Bss\CustomerAttributes\Model\AddressAttributeDependent;
use Bss\CustomerAttributes\Model\HandleData;
use Bss\CustomerAttributes\Model\ResourceModel\Option\Collection as BssCollection;
use Bss\CustomerAttributes\Model\SerializeData;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\View\Element\BlockInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DynamicRow extends AbstractFieldArray
{
    /**
     * @var DynamicRow
     */
    private $optionsRenderer;

    /**
     * @var DynamicRow
     */
    private $dependentRenderer;

    /**
     * @var Edit
     */
    protected $collection;

    /**
     * @var NotDisplay
     */
    private $getAttributes;
    /**
     * @var BssCollection
     */
    private $optionAddressCollection;

    /**
     * @var SerializeData
     */
    private $serializer;

    /**
     * @var HandleData
     */
    private $handleData;

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @param Data $helperData
     * @param Edit $collection
     * @param NotDisplay $getAttributes
     * @param Context $context
     * @param BssCollection $optionAddressCollection
     * @param SerializeData $serializer
     * @param HandleData $handleData
     * @param array $data
     */
    public function __construct(
        Data          $helperData,
        Edit          $collection,
        NotDisplay    $getAttributes,
        Context       $context,
        BssCollection $optionAddressCollection,
        SerializeData $serializer,
        HandleData    $handleData,
        array         $data = []
    ) {
        $this->helperData = $helperData;
        $this->optionAddressCollection = $optionAddressCollection;
        parent::__construct($context, $data);
        $this->collection = $collection;
        $this->getAttributes = $getAttributes;
        $this->serializer = $serializer;
        $this->handleData = $handleData;
    }

    /**
     * Render add Column
     *
     * @throws LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn('attribute_value', ['label' => __('Attribute Value'),
            'class' => 'required-entry ',
            'renderer' => $this->getAttributeValuesRenderer()
        ]);
        $this->addColumn('dependent_attribute', ['label' => __('Dependent Attribute'),
            'class' => 'required-entry ',
            'renderer' => $this->getDependentAttributeRenderer()
        ]);
        $this->setTemplate('Bss_CustomerAttributes::customer/attribute/array.phtml');
        $this->_addAfter = false;
    }

    /**
     * Get Attribute Values to Render
     *
     * @return AddressAttribute|BlockInterface
     * @throws LocalizedException
     */
    private function getAttributeValuesRenderer()
    {
        if (!$this->optionsRenderer) {
            $this->optionsRenderer = $this->getLayout()->createBlock(
                AddressAttribute::class,
                'address_attribute'
            );
        }
        return $this->optionsRenderer;
    }

    /**
     * Get Dependent Attribute
     *
     * @return BlockInterface
     * @throws LocalizedException
     */
    private function getDependentAttributeRenderer()
    {
        if (!$this->dependentRenderer) {
            $this->dependentRenderer = $this->getLayout()->createBlock(
                \Bss\CustomerAttributes\Block\Adminhtml\Address\Edit\Tab\Relation\DependentAddressAttribute::class,
                'dependent_address_attribute'
            );
        }
        return $this->dependentRenderer;
    }

    /**
     * Get Post Data
     *
     * @return AddressAttributeDependent
     */
    public function getDataAttribute()
    {
        return $this->collection->getCollection();
    }

    /**
     * Get All Attributes Collection
     *
     * @return array|AbstractDb|AbstractCollection
     */
    public function getAllAttributesCollection()
    {
        return $this->getAttributes->getAllAddressCollection();
    }

    /**
     * Get Attribute By Code
     *
     * @param mixed $attributeCode
     * @return array|AbstractDb|AbstractCollection
     */
    public function getAttributeByCode($attributeCode)
    {
        return $this->getAttributes->getAddressAttributeByCode($attributeCode);
    }

    /**
     * Get Attribute By Id
     *
     * @return array|AbstractDb|AbstractCollection|null
     */
    public function getAttributeById()
    {
        return $this->collection->getAttributeId();
    }

    /**
     * Get Option Value By Id
     *
     * @param mixed $optionValue
     * @return Collection
     */
    public function getOptionValueById($optionValue)
    {
        return $this->optionAddressCollection->getOptionValueById($optionValue);
    }

    /**
     * Get All Attribute Dependent Information in Be
     *
     * @param array|mixed $attributes
     * @return array
     */
    public function getAllAttributeDependentBe($attributes)
    {
        return $this->handleData->getAllAttributeDependentBe($attributes);
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
        return $this->handleData->validateAllAttributeDependentBe($blockObj, $customerAttributeId);
    }

    /**
     * Encode function
     *
     * @param mixed|array $data
     * @return bool|string
     */
    public function encodeFunction($data)
    {
        return $this->serializer->encodeFunction($data);
    }

    /**
     * Decode function
     *
     * @param mixed|array $data
     * @return array|bool|float|int|string|null
     */
    public function decodeFunction($data)
    {
        return $this->serializer->decodeFunction($data);
    }

    /**
     * Render style
     *
     * @param string $style
     * @param string $selector
     * @return string
     * @throws LocalizedException
     */
    public function renderStyleAsTag(string $style, string $selector): string
    {
        return $this->helperData->renderStyleAsTag($style, $selector);
    }

    /**
     * Render tag
     *
     * @param string $tagName
     * @param array $attributes
     * @param ?string $content
     * @return string
     */
    public function renderTag($tagName, $attributes, $content = null)
    {
        return $this->helperData->renderTag($tagName, $attributes, $content);
    }

    /**
     * Render event listener
     *
     * @param string $eventName
     * @param string $attributeJavascript
     * @param string $elementSelector
     * @return string
     * @throws LocalizedException
     */
    public function renderEventListenerAsTag(
        $eventName,
        $attributeJavascript,
        $elementSelector
    ) {
        return $this->helperData->renderEventListenerAsTag($eventName, $attributeJavascript, $elementSelector);
    }
}
