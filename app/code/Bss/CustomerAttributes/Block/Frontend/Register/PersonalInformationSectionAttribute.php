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

namespace Bss\CustomerAttributes\Block\Frontend\Register;

use Bss\CustomerAttributes\Model\HandleData;
use Magento\Customer\Block\Form\Register;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Eav\Model\ConfigFactory;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as MagentoCollectionFactory;
use Bss\CustomerAttributes\Helper\Data as BssData;
use Bss\CustomerAttributes\Model\AttributeDependent;
use Bss\CustomerAttributes\Model\AttributeDependentRepository;
use Bss\CustomerAttributes\Model\SerializeData;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class PersonalInformationSectionAttribute extends Register
{
    /**
     * @var ConfigFactory
     */
    private $eavAttribute;

    /**
     * @var SerializeData
     */
    private $serializer;

    /**
     * @var AttributeDependentRepository
     */
    protected $attributeDependentRepository;

    /**
     * @var HandleData
     */
    protected $handleData;

    /**
     * @var BssData
     */
    protected $display;

    /**
     * PersonalInformationSectionAttribute constructor.
     *
     * @param Context $context
     * @param Data $directoryHelper
     * @param EncoderInterface $jsonEncoder
     * @param Config $configCacheType
     * @param CollectionFactory $regionCollectionFactory
     * @param MagentoCollectionFactory $countryCollectionFactory
     * @param Manager $moduleManager
     * @param Session $customerSession
     * @param Url $customerUrl
     * @param ConfigFactory $eavAttribute
     * @param AttributeDependentRepository $attributeDependentRepository
     * @param BssData $display
     * @param SerializeData $serializer
     * @param HandleData $handleData
     * @param array $data
     */
    public function __construct(
        Context                      $context,
        Data                         $directoryHelper,
        EncoderInterface             $jsonEncoder,
        Config                       $configCacheType,
        CollectionFactory            $regionCollectionFactory,
        MagentoCollectionFactory     $countryCollectionFactory,
        Manager                      $moduleManager,
        Session                      $customerSession,
        Url                          $customerUrl,
        ConfigFactory                $eavAttribute,
        AttributeDependentRepository $attributeDependentRepository,
        BssData                      $display,
        SerializeData                $serializer,
        HandleData                   $handleData,
        array                        $data = []
    ) {
        $this->eavAttribute = $eavAttribute;
        $this->display = $display;
        $this->serializer = $serializer;
        $this->attributeDependentRepository = $attributeDependentRepository;
        $this->handleData = $handleData;
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $moduleManager,
            $customerSession,
            $customerUrl,
            $data
        );
    }

    /**
     * Check if attribute available show here
     *
     * @param string|int $attributeCode
     * @return bool
     * @throws LocalizedException
     */
    public function isShowIn($attributeCode)
    {
        $attribute = $this->eavAttribute->create()
            ->getAttribute('customer', $attributeCode);
        $usedInForms = $attribute->getUsedInForms();

        if (in_array('personal_infor_section', $usedInForms)) {
            return true;
        }
        return false;
    }

    /**
     * Check if block is CustomerSectionAttribute
     *
     * @return bool
     */
    public function isCustomerSectionAttribute()
    {
        return false;
    }

    /**
     * Get Dependent Data
     *
     * @param mixed|string $attrCode
     * @return AttributeDependent
     */
    public function getDependentData($attrCode)
    {
        return $this->handleData->getDependentData($attrCode);
    }

    /**
     * Display Child Values
     *
     * @return mixed|string
     */
    public function displayChildValues()
    {
        return $this->handleData->displayChildValues();
    }

    /**
     * Check module enable Customer Attribute Dependent
     *
     * @return mixed|string
     */
    public function isEnableCustomerAttributeDependency()
    {
        return $this->handleData->isEnableCustomerAttributeDependency();
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
