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
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Plugin\Integration\Model\Customer;

use Bss\CustomerAttributes\Helper\B2BRegistrationIntegrationHelper;
use Bss\CustomerAttributes\Helper\Customerattribute;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\AttributeMetadataResolver;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\FileUploaderDataResolver;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Ui\Component\Form\Element\Multiline;
use Bss\CustomerAttributes\Model\Config\Source\DisplayBackendCustomerDetail;
use Magento\Framework\Module\Manager;

/**
 * Class DataProviderWithDefaultAddresses
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */

class DataProviderWithDefaultAddresses extends \Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses
{
    /**
     * @var array
     */
    private $loadedData = [];

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * Customer fields that must be removed
     *
     * @var array
     */
    private static $forbiddenCustomerFields = [
        'password_hash',
        'rp_token',
    ];

    /**
     * Allow to manage attributes, even they are hidden on storefront
     *
     * @var bool
     */
    private $allowToShowHiddenAttributes;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @var FileUploaderDataResolver
     */
    private $fileUploaderDataResolver;

    /**
     * @var AttributeMetadataResolver
     */
    private $attributeMetadataResolver;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var B2BRegistrationIntegrationHelper
     */
    protected $b2BRegistrationIntegration;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param Config $eavConfig
     * @param CountryFactory $countryFactory
     * @param SessionManagerInterface $session
     * @param FileUploaderDataResolver $fileUploaderDataResolver
     * @param AttributeMetadataResolver $attributeMetadataResolver
     * @param B2BRegistrationIntegrationHelper $b2BRegistrationIntegration
     * @param Manager $moduleManager
     * @param Http $request
     * @param bool $allowToShowHiddenAttributes
     * @param array $meta
     * @param array $data
     * @param CustomerFactory|null $customerFactory
     * @throws LocalizedException
     */
    public function __construct(
        string                           $name,
        string                           $primaryFieldName,
        string                           $requestFieldName,
        CustomerCollectionFactory        $customerCollectionFactory,
        Config                           $eavConfig,
        CountryFactory                   $countryFactory,
        SessionManagerInterface          $session,
        FileUploaderDataResolver         $fileUploaderDataResolver,
        AttributeMetadataResolver        $attributeMetadataResolver,
        B2BRegistrationIntegrationHelper $b2BRegistrationIntegration,
        Manager $moduleManager,
        Http $request,
                                         $allowToShowHiddenAttributes = true,
        array                            $meta = [],
        array                            $data = [],
        CustomerFactory                  $customerFactory = null
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $customerCollectionFactory,
            $eavConfig,
            $countryFactory,
            $session,
            $fileUploaderDataResolver,
            $attributeMetadataResolver,
            $allowToShowHiddenAttributes,
            $meta,
            $data,
            $customerFactory
        );
        $this->b2BRegistrationIntegration = $b2BRegistrationIntegration;
        $this->moduleManager = $moduleManager;
        $this->request = $request;
        $this->collection = $customerCollectionFactory->create();
        $this->collection->addAttributeToSelect('*');
        $this->allowToShowHiddenAttributes = $allowToShowHiddenAttributes;
        $this->session = $session;
        $this->countryFactory = $countryFactory;
        $this->fileUploaderDataResolver = $fileUploaderDataResolver;
        $this->attributeMetadataResolver = $attributeMetadataResolver;
        $this->meta['customer']['children'] = $this->getAttributesMeta(
            $eavConfig->getEntityType('customer')
        );
        $this->customerFactory = $customerFactory ?: ObjectManager::getInstance()->get(CustomerFactory::class);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData(): array
    {
        return parent::getData();
    }

    /**
     * Prepare default address data.
     *
     * @param Address|false $address
     * @return array
     */
    private function prepareDefaultAddress($address): array
    {
        if (!$address) {
            return [];
        }

        $addressData = $address->getData();
        if (isset($addressData['street']) && !is_array($addressData['street'])) {
            $addressData['street'] = explode("\n", $addressData['street']);
        }
        if (!empty($addressData['country_id'])) {
            $addressData['country'] = $this->countryFactory->create()
                ->loadByCode($addressData['country_id'])
                ->getName();
        }
        $addressData['region'] = $address->getRegion();

        return $addressData;
    }

    /***
     * Prepare values for Custom Attributes.
     *
     * @param array $data
     * @return void
     */
    private function prepareCustomAttributeValue(array &$data): void
    {
        foreach ($this->meta['customer']['children'] as $attributeName => $attributeMeta) {
            if ($attributeMeta['arguments']['data']['config']['dataType'] === Multiline::NAME
                && isset($data[$attributeName])
                && !is_array($data[$attributeName])
            ) {
                $data[$attributeName] = explode("\n", $data[$attributeName]);
            }
        }
    }

    /**
     * Get attributes meta
     *
     * @param Type $entityType
     * @return array
     * @throws LocalizedException
     */
    private function getAttributesMeta(Type $entityType): array
    {
        $meta = [];
        $customerB2b = [Customerattribute::B2B_PENDING, Customerattribute::B2B_APPROVAL, Customerattribute::B2B_REJECT];
        $attributes = $entityType->getAttributeCollection();
        /* @var AbstractAttribute $attribute */
        foreach ($attributes as $attribute) {
            $meta[$attribute->getAttributeCode()] = $this->attributeMetadataResolver->getAttributesMeta(
                $attribute,
                $entityType,
                $this->allowToShowHiddenAttributes
            );
        }
        $this->attributeMetadataResolver->processWebsiteMeta($meta);
        if (!$this->moduleManager->isEnabled('Bss_B2bRegistration')) {
            return $meta;
        }
        $params = $this->request->getParams();
        $attributes = $entityType->getAttributeCollection();
        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            if ($attributeCode == "b2b_activasion_status") {
                continue;
            }
            $usedInForms = $attribute->getUsedInForms();
            if (in_array('is_customer_attribute', $usedInForms) && isset($params['id'])) {
                $customerId = $params['id'];
                if (isset($this->getData()[$customerId]['customer']['b2b_activasion_status']) &&
                    in_array($this->getData()[$customerId]['customer']['b2b_activasion_status'], $customerB2b)) {
                    /* B2b Customer */
                    if ($this->b2BRegistrationIntegration->getAttributeDisplay($attributeCode) ==
                        DisplayBackendCustomerDetail::NORMAL_ACCOUNTS
                    ) {
                        unset($meta[$attributeCode]);
                    }
                } else {
                    /* Normal Account */
                    if ($this->b2BRegistrationIntegration->getAttributeDisplay($attributeCode) ==
                        DisplayBackendCustomerDetail::B2B_ACCOUNTS) {
                        unset($meta[$attributeCode]);
                    }
                }
            }
        }
        return $meta;
    }
}
