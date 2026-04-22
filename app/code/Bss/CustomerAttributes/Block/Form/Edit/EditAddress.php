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

namespace Bss\CustomerAttributes\Block\Form\Edit;

use Bss\CustomerAttributes\Model\HandleData;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Helper\Address;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as MagentoCollectionFactory;
use Magento\Directory\Helper\Data as MagentoData;
use Bss\CustomerAttributes\Helper\Data;
use Bss\CustomerAttributes\Model\AddressAttributeDependent;
use Bss\CustomerAttributes\Model\AddressAttributeDependentRepository;
use Bss\CustomerAttributes\Model\SerializeData;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class EditAddress extends \Magento\Customer\Block\Address\Edit
{
    /**
     * @var AddressAttributeDependentRepository
     */
    protected $addressAttributeDependentRepository;

    /**
     * @var GetAttributeValues
     */
    protected $getAttribute;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var SerializeData
     */
    private $serializer;

    /**
     * @var HandleData
     */
    protected $handleData;

    /**
     * @param Context $context
     * @param MagentoData $directoryHelper
     * @param EncoderInterface $jsonEncoder
     * @param Config $configCacheType
     * @param CollectionFactory $regionCollectionFactory
     * @param MagentoCollectionFactory $countryCollectionFactory
     * @param Session $customerSession
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterfaceFactory $addressDataFactory
     * @param CurrentCustomer $currentCustomer
     * @param DataObjectHelper $dataObjectHelper
     * @param AddressAttributeDependentRepository $addAttributeDependent
     * @param GetAttributeValues $getAttribute
     * @param Data $helperData
     * @param SerializeData $serializer
     * @param HandleData $handleData
     * @param array $data
     * @param AddressMetadataInterface|null $addressMetadata
     * @param Address|null $addressHelper
     */
    public function __construct(
        Context                             $context,
        MagentoData                         $directoryHelper,
        EncoderInterface                    $jsonEncoder,
        Config                              $configCacheType,
        CollectionFactory                   $regionCollectionFactory,
        MagentoCollectionFactory            $countryCollectionFactory,
        Session                             $customerSession,
        AddressRepositoryInterface          $addressRepository,
        AddressInterfaceFactory             $addressDataFactory,
        CurrentCustomer                     $currentCustomer,
        DataObjectHelper                    $dataObjectHelper,
        AddressAttributeDependentRepository $addAttributeDependent,
        GetAttributeValues                  $getAttribute,
        Data                                $helperData,
        SerializeData                       $serializer,
        HandleData                          $handleData,
        array                               $data = [],
        AddressMetadataInterface            $addressMetadata = null,
        Address                             $addressHelper = null
    ) {
        $this->addressAttributeDependentRepository = $addAttributeDependent;
        $this->getAttribute = $getAttribute;
        $this->helperData = $helperData;
        $this->serializer = $serializer;
        $this->handleData = $handleData;
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $customerSession,
            $addressRepository,
            $addressDataFactory,
            $currentCustomer,
            $dataObjectHelper,
            $data,
            $addressMetadata
        );
    }

    /**
     * Get Dependent Data
     *
     * @param mixed $attrCode
     * @return AddressAttributeDependent
     */
    public function getAddressDependentData($attrCode)
    {
        return $this->addressAttributeDependentRepository->getDataByAttrID($attrCode);
    }

    /**
     * Get Attribute Values
     *
     * @param mixed $attribute
     * @return array|null
     */
    public function getAddressAttributeValues($attribute)
    {
        $value = $this->getAddressDependentData($attribute->getData('attribute_id'))->getData();
        return $this->getAttribute->getAttributeValues($value);
    }

    /**
     * Check module enable Customer Attribute Dependent
     *
     * @return mixed
     */
    public function isEnableCustomerAttributeDependency()
    {
        return $this->helperData->isEnableCustomerAttributeDependency();
    }

    /**
     * Get List Dependent AttributeCode
     *
     * @param mixed|array $attrData
     * @return array
     */
    public function getListDependentAttributeCode($attrData)
    {
        return $this->handleData->getListDependentAttributeCode($attrData);
    }

    /**
     * Get List DisableAttribute
     *
     * @param mixed|array $attrData
     * @return array
     */
    public function getListDisableAttribute($attrData)
    {
        return $this->handleData->getListDisableAttribute($attrData);
    }

    /**
     * Get List AttributeId NotShow
     *
     * @param mixed|array $attrData
     * @return array
     */
    public function getListAttributeIdNotShow($attrData)
    {
        return $this->handleData->getListAttributeIdNotShow($attrData);
    }

    /**
     * Validate data dependent
     *
     * @param mixed|array $attributeValues
     * @param mixed|array $attributeCollectionData
     */
    public function validateDependentAttributeInfo($attributeValues, $attributeCollectionData)
    {
        return $this->handleData->validateDependentAttributeInfo($attributeValues, $attributeCollectionData);
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
}
