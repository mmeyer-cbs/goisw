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
declare(strict_types=1);

namespace Bss\CustomerAttributes\Block\Adminhtml\Order\Create\Billing;

use Bss\CustomerAttributes\Block\Form\Field\DynamicRow;
use Bss\CustomerAttributes\Helper\Customer\Grid\NotDisplay;
use Bss\CustomerAttributes\Helper\Data;
use Bss\CustomerAttributes\Model\AddressAttributeDependent;
use Bss\CustomerAttributes\Model\AddressAttributeDependentRepository;
use Bss\CustomerAttributes\Model\SerializeData;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Model\Address\Mapper;
use Magento\Customer\Model\Options;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\Form\AbstractForm;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Sales\Model\AdminOrder\Create;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class Address extends \Magento\Sales\Block\Adminhtml\Order\Create\Billing\Address
{
    /**
     * @var NotDisplay
     */
    protected $getAttributes;
    /**
     * @var Data
     */
    protected $helperData;
    /**
     * @var SerializeData
     */
    private $serializer;
    /**
     * @var AddressAttributeDependentRepository
     */
    protected $addressAttributeDependentRepository;

    /**
     * @var DynamicRow
     */
    protected $dynamicRowBlock;

    /**
     * @param DynamicRow $dynamicRowBlock
     * @param Context $context
     * @param Quote $sessionQuote
     * @param Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param FormFactory $formFactory
     * @param DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param EncoderInterface $jsonEncoder
     * @param \Magento\Customer\Model\Metadata\FormFactory $customerFormFactory
     * @param Options $options
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param AddressRepositoryInterface $addressService
     * @param SearchCriteriaBuilder $criteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param Mapper $addressMapper
     * @param NotDisplay $getAttributes
     * @param AddressAttributeDependentRepository $addressAttributeDependentRepository
     * @param Data $helperData
     * @param SerializeData $serializer
     * @param array $data
     */
    public function __construct(
        DynamicRow                                   $dynamicRowBlock,
        Context                                      $context,
        Quote                                        $sessionQuote,
        Create                                       $orderCreate,
        PriceCurrencyInterface                       $priceCurrency,
        FormFactory                                  $formFactory,
        DataObjectProcessor                          $dataObjectProcessor,
        \Magento\Directory\Helper\Data               $directoryHelper,
        EncoderInterface                             $jsonEncoder,
        \Magento\Customer\Model\Metadata\FormFactory $customerFormFactory,
        Options                                      $options,
        \Magento\Customer\Helper\Address             $addressHelper,
        AddressRepositoryInterface                   $addressService,
        SearchCriteriaBuilder                        $criteriaBuilder,
        FilterBuilder                                $filterBuilder,
        Mapper                                       $addressMapper,
        NotDisplay                                   $getAttributes,
        AddressAttributeDependentRepository          $addressAttributeDependentRepository,
        Data                                         $helperData,
        SerializeData                                $serializer,
        array                                        $data = []
    ) {
        $this->dynamicRowBlock = $dynamicRowBlock;
        $this->getAttributes = $getAttributes;
        $this->addressAttributeDependentRepository = $addressAttributeDependentRepository;
        $this->helperData = $helperData;
        $this->serializer = $serializer;
        parent::__construct(
            $context,
            $sessionQuote,
            $orderCreate,
            $priceCurrency,
            $formFactory,
            $dataObjectProcessor,
            $directoryHelper,
            $jsonEncoder,
            $customerFormFactory,
            $options,
            $addressHelper,
            $addressService,
            $criteriaBuilder,
            $filterBuilder,
            $addressMapper,
            $data
        );
    }

    /**
     * Add Attributes To Form
     *
     * @param AttributeMetadataInterface[] $attributes
     * @param AbstractForm $form
     * @return $this
     */
    protected function _addAttributesToForm($attributes, AbstractForm $form)
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

    /**
     * Get All Address Attribute
     *
     * @return AttributeMetadataInterface[]
     */
    public function getAddressAttribute()
    {
        $addressForm = $this->_customerFormFactory->create('customer_address', 'adminhtml_customer_address');
        $attributes = $addressForm->getAttributes();
        return $attributes;
    }

    /**
     * Get Attribute By Code
     *
     * @param mixed|string $attributeCode
     * @return array|AbstractDb|AbstractCollection
     */
    public function getAttributeByCode($attributeCode)
    {
        return $this->getAttributes->getAddressAttributeByCode($attributeCode);
    }

    /**
     * Get Address Dependent Data
     *
     * @param string|mixed $attributeCode
     * @return AddressAttributeDependent
     */
    public function getAddressDependentAttribute($attributeCode)
    {
        return $this->addressAttributeDependentRepository->getDataByAttrID($attributeCode);
    }

    /**
     * Check module enable Customer Attribute Dependent
     *
     * @return mixed|string
     */
    public function isEnableCustomerAttributeDependency()
    {
        return $this->helperData->isEnableCustomerAttributeDependency();
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
     * Same 100% core
     *
     * @param string $style
     * @param string $selector
     * @return string
     * @throws LocalizedException
     */
    public function renderStyleAsTag($style, $selector)
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
    public function renderTag($tagName, $attributes, $content = null, $textContent = true)
    {
        return $this->helperData->renderTag($tagName, $attributes, $content, $textContent);
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
