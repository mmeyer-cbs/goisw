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
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomerAttributes\Helper;

use Bss\CustomerAttributes\Model\Config\Source\DisplayBackendCustomerDetail;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * @SuppressWarnings(PHPMD)
 */
class Customerattribute extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const NORMAL_ACCOUNT = 0;
    public const B2B_PENDING = 1;
    public const B2B_APPROVAL = 2;
    public const B2B_REJECT = 3;
    public const CUSTOMER_ADDRESS = 'customer_address';
    public const CUSTOMER = 'customer';
    public const USER_DEFINED = 1;

    /**
     * @var array
     */
    protected $convertAddressCollection;

    /**
     * @var bool|null
     */
    protected $checkCustomerB2B = null;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Collection|null
     */
    protected $addressCollection = null;

    /**
     * @var \Magento\Framework\File\Size
     */
    protected $fileSize;

    /**
     * Attribute factory
     *
     * @var \Magento\Customer\Model\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * Eav attribute factory
     * @var \Magento\Eav\Model\Config
     */
    protected $eavAttribute;

    /**
     * Store factory
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Api\CustomerMetadataInterface
     */
    protected $metadata;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var SaveObject
     */
    protected $saveObject;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;
    /**
     * @var AttributeCollectionFactory
     */
    private $attributeCollectionFactory;
    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    private $attributeRepository;
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * Customerattribute constructor.
     * @param Session $customerSession
     * @param \Magento\Framework\File\Size $fileSize
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\AttributeFactory $attributeFactory
     * @param AttributeCollectionFactory $attributeCollectionFactory
     * @param \Magento\Eav\Model\ConfigFactory $eavAttributeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Api\CustomerMetadataInterface $metadata
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param SaveObject $saveObject
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        Session                                           $customerSession,
        \Magento\Framework\File\Size                      $fileSize,
        \Magento\Framework\App\Helper\Context             $context,
        \Magento\Customer\Model\AttributeFactory          $attributeFactory,
        AttributeCollectionFactory                        $attributeCollectionFactory,
        \Magento\Eav\Model\ConfigFactory                  $eavAttributeFactory,
        \Magento\Store\Model\StoreManagerInterface        $storeManager,
        \Magento\Customer\Api\CustomerMetadataInterface   $metadata,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Bss\CustomerAttributes\Helper\SaveObject         $saveObject,
        \Magento\Framework\App\ProductMetadataInterface   $productMetadata,
        \Magento\Framework\Serialize\Serializer\Json      $json,
        \Magento\Eav\Api\AttributeRepositoryInterface     $attributeRepository,
        TimezoneInterface                                 $timezone
    ) {
        $this->customerSession = $customerSession;
        $this->fileSize = $fileSize;
        parent::__construct($context);
        $this->attributeFactory = $attributeFactory;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->eavAttribute = $eavAttributeFactory;
        $this->storeManager = $storeManager;
        $this->metadata = $metadata;
        $this->customerRepository = $customerRepository;
        $this->urlEncoder = $context->getUrlEncoder();
        $this->saveObject = $saveObject;
        $this->productMetadata = $productMetadata;
        $this->json = $json;
        $this->attributeRepository = $attributeRepository;
        $this->timezone = $timezone;
    }

    /**
     * Get Config
     *
     * @param string $path
     * @param int $store
     * @param string $scope
     * @return mixed
     */
    public function getConfig($path, $store = null, $scope = null)
    {
        if ($scope === null) {
            $scope = $this->saveObject->returnScopeStore();
        }
        return $this->scopeConfig->getValue($path, $scope, $store);
    }

    /**
     * Check is module enabled
     *
     * @return mixed
     */
    public function isEnable()
    {
        return $this->getConfig('bss_customer_attribute/general/enable');
    }

    /**
     * Get Tittle
     *
     * @return string
     */
    public function getTitle()
    {
        return (string)$this->getConfig('bss_customer_attribute/general/title');
    }

    /**
     * Return user defined attributes attributes
     *
     * @return mixed
     */
    public function getUserDefinedAttributes()
    {
        $entityTypeId = $this->saveObject->returnSaveObjectMore()->returnEntityFactory()->create()
            ->setType(\Magento\Customer\Model\Customer::ENTITY)
            ->getTypeId();
        $attribute = $this->attributeFactory->create()
            ->setEntityTypeId($entityTypeId);
        $collection = $attribute->getCollection()
            ->addFieldToFilter('is_user_defined', self::USER_DEFINED)
            ->setOrder('sort_order', 'ASC');
        return $collection;
    }

    /**
     * Return user defined address attributes
     *
     * @param int $isVisible
     *
     * @return Collection
     */
    public function getAddressCollection($isVisible = 1)
    {
        if (!$this->addressCollection) {
            $entityTypeId = $this->saveObject->returnSaveObjectMore()->returnEntityFactory()->create()
                ->setType(self::CUSTOMER_ADDRESS)
                ->getTypeId();
            $collection = $this->attributeCollectionFactory->create();
            $this->addressCollection = $collection->setEntityTypeFilter($entityTypeId)
                ->addFieldToFilter('is_user_defined', self::USER_DEFINED)
                ->setOrder('sort_order', 'ASC');
        }
        return $this->addressCollection;
    }

    /**
     * Improve performance when find attribute code
     *
     * @param int $isVisible
     *
     * @return array
     */
    public function converAddressCollectioin($isVisible = 1)
    {
        if (!$this->convertAddressCollection) {
            $attributesAddress = $this->getAddressCollection($isVisible);
            $data = [];
            foreach ($attributesAddress as $attributeAddress) {
                $data[$attributeAddress->getAttributeCode()] = $attributeAddress;
            }
            $this->convertAddressCollection = $data;
        }

        return $this->convertAddressCollection;
    }

    /**
     * Return is customer address attribute
     *
     * @param $attributeCode
     * @return bool
     * @throws LocalizedException
     */
    public function isCustomAddressAttribute($attributeCode)
    {
        try {
            $attribute = $this->eavAttribute->create()
                ->getAttribute('customer_address', $attributeCode);
            if ($attribute->getIsUserDefined()) {
                return true;
            } else {
                return false;
            }
        } catch (LocalizedException $e) {
            return false;
        }
    }

    /**
     * Return is customer address visible
     *
     * @param $attributeCode
     * @return bool
     * @throws LocalizedException
     */
    public function isVisible($attributeCode)
    {
        try {
            $attribute = $this->attributeRepository->get(self::CUSTOMER_ADDRESS, $attributeCode);
        } catch (NoSuchEntityException $e) {
            throw $e;
        }
        return $attribute->getIsVisible();
    }

    /**
     * @return bool
     */
    public function isEnableB2bRegistration()
    {
        return $this->isModuleOutputEnabled('Bss_B2bRegistration');
    }

    /**
     * @return bool
     */
    public function isB2bRegistrationPage()
    {
        $actionName = $this->_request->getFullActionName();
        return $actionName == 'btwob_account_create';
    }

    /**
     * Check Attribute use in account create
     *
     * @param string $attributeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAttribureForCustomerAccountCreate($attributeCode)
    {
        $attribute = $this->attributeRepository->get(self::CUSTOMER, $attributeCode);
        $usedInForms = $attribute->getUsedInForms();

        if (!$this->isB2bRegistrationPage() && in_array('customer_account_create_frontend', $usedInForms)) {
            return true;
        }
        return false;
    }

    /**
     * Check Attribute use in B2B account create
     *
     * @param string $attributeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAttribureForB2BCustomerAccountCreate($attributeCode)
    {
        $attribute = $this->attributeRepository->get(self::CUSTOMER, $attributeCode);
        $usedInForms = $attribute->getUsedInForms();

        if ($this->isEnableB2bRegistration() && in_array('b2b_account_create', $usedInForms) &&
            $this->isB2bRegistrationPage()
        ) {
            return true;
        }
        return false;
    }

    /**
     * Check Attribute use in account Edit
     *
     * @param string $attributeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAttribureForCustomerAccountEdit($attributeCode)
    {
        $attribute = $this->attributeRepository->get(self::CUSTOMER, $attributeCode);
        $usedInForms = $attribute->getUsedInForms();
        if (!$this->checkDisplayInB2bOrNormalAccount($attributeCode)) {
            return false;
        }
        if (in_array('customer_account_edit_frontend', $usedInForms)) {
            return true;
        }
        return false;
    }

    /**
     * Check Attribute use in Order Detail
     *
     * @param string $attributeCode
     * @param int $customerId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAttribureForOrderDetail($attributeCode, $customerId = null)
    {
        $attribute = $this->attributeRepository->get(self::CUSTOMER, $attributeCode);
        $usedInForms = $attribute->getUsedInForms();
        if (!$this->checkDisplayInB2bOrNormalAccount($attributeCode, $customerId)) {
            return false;
        }
        if (in_array('order_detail', $usedInForms)) {
            return true;
        }
        return false;
    }

    /**
     * Check Attribute use in Admin Order Detail
     *
     * @param string $attributeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAttributeForAdminOrderDetail($attributeCode)
    {
        $attribute = $this->attributeRepository->get(self::CUSTOMER_ADDRESS, $attributeCode);
        $usedInForms = $attribute->getUsedInForms();
        if (!$this->checkDisplayInB2bOrNormalAccount($attributeCode)) {
            return false;
        }
        if (in_array('adminhtml_order_detail', $usedInForms)) {
            return true;
        }
        return false;
    }

    /**
     * Check Attribute use in Email
     *
     * @param string $attributeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAttribureAddtoEmail($attributeCode)
    {
        $attribute = $this->attributeRepository->get(self::CUSTOMER, $attributeCode);
        $usedInForms = $attribute->getUsedInForms();
        if (!$this->checkDisplayInB2bOrNormalAccount($attributeCode)) {
            return false;
        }
        if (in_array('show_in_email', $usedInForms)) {
            return true;
        }
        return false;
    }

    /**
     * Check Attribute use in New Account Email
     *
     * @param string $attributeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAttribureAddtoEmailNewAccount($attributeCode)
    {
        $attribute = $this->attributeRepository->get(self::CUSTOMER, $attributeCode);
        $usedInForms = $attribute->getUsedInForms();
        if (!$this->checkDisplayInB2bOrNormalAccount($attributeCode)) {
            return false;
        }
        if (in_array('show_in_email_new_account', $usedInForms)) {
            return true;
        }
        return false;
    }

    /**
     * Check Attribute use in Order Detail Frontend
     *
     * @param string $attributeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAttribureAddtoOrderFrontend($attributeCode)
    {
        $attribute = $this->attributeRepository->get(self::CUSTOMER, $attributeCode);
        $usedInForms = $attribute->getUsedInForms();
        if (!$this->checkDisplayInB2bOrNormalAccount($attributeCode)) {
            return false;
        }
        if (in_array('show_order_frontend', $usedInForms)) {
            return true;
        }
        return false;
    }

    /**
     * Check Attribute use in Checkout
     *
     * @param string $attributeCode
     * @return bool
     */
    public function isAttribureAddtoCheckout($attributeCode)
    {
        try {
            $attribute = $this->attributeRepository->get(self::CUSTOMER, $attributeCode);
            $usedInForms = $attribute->getUsedInForms();
        } catch (\Exception $exception) {
            $this->_logger->critical($exception->getMessage());
        }
        if (!$this->checkDisplayInB2bOrNormalAccount($attributeCode)) {
            return false;
        }
        if (isset($usedInForms) && in_array('show_checkout_frontend', $usedInForms)) {
            return true;
        }
        return false;
    }

    /**
     * Check Address use in Checkout
     *
     * @param string $attributeCode
     * @return bool
     */
    public function isAddressAddToCheckout($attributeCode)
    {
        try {
            $attribute = $this->attributeRepository->get(self::CUSTOMER_ADDRESS, $attributeCode);
            $usedInForms = $attribute->getUsedInForms();
            if (in_array('show_checkout_frontend', $usedInForms)) {
                return true;
            }
        } catch (\Exception $exception) {
            $this->_logger->critical($exception->getMessage());
        }
        return false;
    }

    /**
     * Check Address use in address book
     *
     * @param string $attributeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isAddressShowInBook($attributeCode)
    {
        $attribute = $this->attributeRepository->get(self::CUSTOMER_ADDRESS, $attributeCode);
        $usedInForms = $attribute->getUsedInForms();

        if (in_array('customer_address_edit', $usedInForms)) {
            return true;
        }
        return false;
    }

    /**
     * Check is show address in order detail
     *
     * @param string $attributeCode
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isAddressShowInOrderDetail($attributeCode)
    {
        $attribute = $this->attributeRepository->get(self::CUSTOMER_ADDRESS, $attributeCode);
        $usedInForms = $attribute->getUsedInForms();

        if ($attribute->getIsVisible() && in_array('order_detail', $usedInForms)) {
            return true;
        }
        return false;
    }

    /**
     * Check Hide Field If Fill Before
     *
     * @param string $attributeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isHideIfFill($attributeCode)
    {
        $attribute = $this->attributeRepository->get(self::CUSTOMER, $attributeCode);
        $usedInForms = $attribute->getUsedInForms();

        if (in_array('hide_if_fill_frontend', $usedInForms)) {
            return true;
        }
        return false;
    }

    /**
     * Get Store Id
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getStoreId();
    }

    /**
     * Get Attribute Options
     *
     * @param string $attributeCode
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttributeOptions($attributeCode)
    {
        $customerEntity = \Magento\Customer\Model\Customer::ENTITY;
        $options = $this->eavAttribute->create()->getAttribute($customerEntity, $attributeCode)
            ->getSource()->getAllOptions();
        return $options;
    }

    /**
     * Get Address Attribute Options
     *
     * @param string $attributeCode
     * @return array
     */
    public function getAddressAttributeOptions($attributeCode)
    {
        $customerEntity = 'customer_address';
        $options = [];
        try {
            $options = $this->eavAttribute->create()->getAttribute($customerEntity, $attributeCode)
                ->getSource()->getAllOptions();
        } catch (\Exception $exception) {
            $this->_logger->critical($exception->getMessage());
        }
        return $options;
    }

    /**
     * Get loged in customer data
     *
     * @param int $customerId
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer($customerId)
    {
        $customer = $this->saveObject->returnSaveObjectMore()->returnCustomerFactory()->create()->load($customerId);
        return $customer;
    }

    /**
     * Check Attribute Has Data
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Magento\Customer\Model\Attribute $attributes
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function hasDataCustomerAttributesOrderFrontend($customer, $attributes)
    {
        foreach ($attributes as $attribute) {
            if ($attribute->isSystem() || !$attribute->isUserDefined()) {
                continue;
            }
            if ($this->isAttribureAddtoOrderFrontend($attribute->getAttributeCode())) {
                if ($customer->getCustomAttribute($attribute->getAttributeCode())) {
                    if ($customer->getCustomAttribute($attribute->getAttributeCode())->getValue() != '') {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Get Attribute Html
     *
     * @param string $idCustomer
     * @param $order
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAttributesHtml($idCustomer, $order)
    {
        $html = '';
        if ($this->getConfig('bss_customer_attribute/general/enable') && $idCustomer) {
            $entityTypeId = 'customer';
            $attributes = $this->metadata->getAllAttributesMetadata($entityTypeId);
            $customer = $this->customerRepository->getById($idCustomer);
            if ($this->hasDataCustomerAttributesOrderFrontend($customer, $attributes)) {
                $html = '<h3>' . $this->getTitle() . '</h3>';
                foreach ($attributes as $attribute) {
                    $displayOrderDetail = (bool)$this->isAttribureForOrderDetail($attribute->getAttributeCode(), $idCustomer);
                    if ($attribute->isSystem()
                        || !$attribute->isUserDefined()
                        || !$attribute->isVisible()
                        || !$displayOrderDetail
                    ) {
                        continue;
                    }
                    if ($this->isAttribureAddtoOrderFrontend($attribute->getAttributeCode())) {
                        $orderKey = sprintf('customer_%s', $attribute->getAttributeCode());
                        if ($order->getData($orderKey) != '') {
                            $html .= $this->getValueAttribute($attribute, $order->getData($orderKey));
                        }
                    }
                }
            }
        }
        return $html;
    }

    /**
     * Get address front end label for store view
     * @param $attributeCode
     * @return string
     * @throws LocalizedException
     */
    public function getAddressFrontEndLabel($attributeCode)
    {
        $attribute = $this->attributeRepository->get(self::CUSTOMER_ADDRESS, $attributeCode);
        return $attribute->getStoreLabel($this->getStoreId());
    }

    /**
     * Check is custom attribute existed
     * @param $attributeCode
     * @return bool
     * @throws LocalizedException
     */
    public function isAttributeExist($attributeCode)
    {
        $attribute = $this->eavAttribute->create()
            ->getAttribute('customer_address', $attributeCode);
        if ($attribute->getAttributeId() && $attribute) {
            return true;
        }
        return false;
    }

    /**
     * @param $attribute
     * @param $attributeValue
     * @return string
     */
    private function getValueAttribute($attribute, $attributeValue)
    {
        $html = '';
        if ($attribute->getOptions()) {
            $valueOption = $attributeValue;
            $valueOption = explode(",", $valueOption);
            $label = "";
            foreach ($valueOption as $value) {
                foreach ($attribute->getOptions() as $option) {
                    if ($value == $option->getValue()) {
                        $label .= $option->getLabel() . ",";
                    }
                }
            }
            if (!empty($label)) {
                $html .= "<div class=\"orderAttribute\"><div class=\"label_attribute\"><span>" .
                    $attribute->getFrontendLabel() . ' : ' . "</span></div>" . "<div class=\"value_attribute\"><span>" .
                    rtrim($label, ",") . "</span></div></div><br/>";
            }
        } else {
            $valueAttribute = $attributeValue;
            $html .= $this->getAttributeFileinOrderFront($attribute, $valueAttribute);
        }
        return $html;
    }

    /**
     * @param $attributeCode
     * @param $attributeValue
     * @return string
     * @throws NoSuchEntityException
     */
    public function getValueAddressAttributeOption($attributeCode, $attributeValue)
    {
        if ($attributeValue !== '') {
            if (is_array($attributeValue)) {
                $attributeValue = $attributeValue['value'];
            }
            $valueOption = explode(",", $attributeValue);
            $attribute = $this->attributeRepository->get(self::CUSTOMER_ADDRESS, $attributeCode);
            if ($attribute->getOptions()) {
                $label = '';
                foreach ($valueOption as $value) {
                    foreach ($attribute->getOptions() as $option) {
                        if ($value == $option->getValue()) {
                            $label .= $option->getLabel() . ",";
                        }
                    }
                }
                return rtrim($label, ",");
            } elseif ($attribute->getFrontendInput() == 'file') {
                return 'file';
            }
        }
        return false;
    }

    /**
     * @param $attribute
     * @param $attributeValue
     * @return string
     */
    public function getValueAddressAttributeForOrder($attribute, $attributeValue)
    {
        if ($attributeValue !== '') {
            if (is_array($attributeValue)) {
                if (isset($attributeValue['value'])) {
                    $attributeValue = $attributeValue['value'];
                } else {
                    $attributeValue = implode(',', $attributeValue);
                }
            }
            $valueOption = explode(",", $attributeValue);
            if ($attribute->getOptions()) {
                $label = '';
                foreach ($valueOption as $value) {
                    foreach ($attribute->getOptions() as $option) {
                        if ($value == $option->getValue()) {
                            $label .= $option->getLabel() . ",";
                        }
                    }
                }
                return rtrim($label, ",");
            } elseif ($attribute->getFrontendInput() == 'file') {
                $file = $this->getViewFile($attributeValue, self::CUSTOMER_ADDRESS);
                $fileName = $this->getFileName($attributeValue);
                return '<a href="' . $file . '">' . $fileName . '</a>';
            }
        }
        return $attributeValue;
    }

    /**
     * Get date format for all page
     *
     * @param null|string $date
     * @return string
     */
    public function formatDateTime($date = null)
    {
        return $date ? $this->timezone->date($date)->format('d/m/Y H:i:s') : '';
    }

    /**
     * Get date format for all page
     *
     * @param null|string $date
     * @return string
     * @throws \Exception
     */
    public function formatDate($date = null)
    {
        return $date ? (new \DateTime($date))->format('m/d/Y') : '';
    }

    /**
     * Check Is Date Time
     *
     * @param string $value
     * @param mixed|null $attributeCode
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isDateFormat($value, $attributeCode = null)
    {
        $attType = '';
        if ($attributeCode) {
            $attribute = $this->attributeRepository->get(self::CUSTOMER_ADDRESS, $attributeCode);
            $attType = $attribute->getFrontendInput();
        }
        if ($attType == 'date' && $value && strtotime($value)) {
            return true;
        }
        return false;
    }

    /**
     * @param Attribute $attribute
     * @param string $valueAttribute
     * @return string
     */
    private function getAttributeFileinOrderFront($attribute, $valueAttribute)
    {
        $html = "";
        if ($attribute->getFrontendInput() == 'file') {
            if (!$this->getConfig("bss_customer_attribute/general/allow_download_file")) {
                $noDownload = "controlsList =\"nodownload\" ";
            } else {
                $noDownload = " ";
            }

            if (!$this->getConfig("bss_customer_attribute/general/allow_download_file")) {
                $noDownloadFile = "class=\"disabled\"";
            } else {
                $noDownloadFile = " ";
            }

            if (preg_match("/\.(gif|png|jpg)$/", $valueAttribute)) {
                $html .= $this->getFileImageFrontend($attribute, $valueAttribute);
            } elseif (preg_match("/\.(mp4|3gb|mov|mpeg)$/", $valueAttribute)) {
                $html .= $this->getFileVideoFrontend($attribute, $valueAttribute, $noDownload);
            } elseif (preg_match("/\.(mp3|ogg|wav)$/", $valueAttribute)) {
                $html .= $this->getFileAudioFrontend($attribute, $valueAttribute, $noDownload);
            } else {
                $html .= $this->getFileOtherFrontend($attribute, $valueAttribute, $noDownloadFile);
            }
        } else {
            $html .= "<div class=\"orderAttribute\"><div class=\"label_attribute\"><span>" .
                $attribute->getFrontendLabel() . ': ' . "</span></div>" . "<div class=\"value_attribute\"><span>" .
                $valueAttribute . "</span></div></div><br/>";
        }

        return $html;
    }

    /**
     * @param Attribute $attribute
     * @param string $valueAttribute
     * @return string
     */
    private function getFileImageFrontend($attribute, $valueAttribute)
    {
        $tagA = "";
        $endTagA = "";
        if ($this->getConfig("bss_customer_attribute/general/allow_download_file")) {
            $tagA = "<a href=\"" . $this->getViewFile($valueAttribute) . "\"" . " target=\"_blank\" >";
            $endTagA = "</a>";
        }
        $html = "<div class=\"orderAttribute\"><div class=\"label_attribute\"><span>" .
            $attribute->getFrontendLabel() . ': ' . "</span></div>" .
            $tagA . "<div class=\"value_attribute\"><img src=\"" .
            $this->getViewFile($valueAttribute) . "\" alt=\""
            . $this->getFileName($valueAttribute) . "\" width=\"200\" /></div>" .
            "</div>" . $endTagA . "<br/>";
        return $html;
    }

    /**
     * @param Attribute $attribute
     * @param string $valueAttribute
     * @param string $noDownload
     * @return string
     */
    private function getFileVideoFrontend($attribute, $valueAttribute, $noDownload)
    {
        $html = "<div class=\"orderAttribute\"><div class=\"label_attribute\"><span>" .
            $attribute->getFrontendLabel() . ': ' .
            "</span></div>" . "<div class=\"value_attribute\">
            <video width=\"400\" height=\"100\" " . $noDownload . " controls>" .
            "<source src=\"" . $this->getViewFile($valueAttribute) . "\" type=\"video/mp4\">
            <source src=\"" . $this->getViewFile($valueAttribute) . "\" type=\"video/ogg\">
            Your browser does not support HTML5 video.
            </video></div>" .
            "</div><br/>";

        return $html;
    }

    /**
     * @param Attribute $attribute
     * @param string $valueAttribute
     * @param string $noDownload
     * @return string
     */
    private function getFileAudioFrontend($attribute, $valueAttribute, $noDownload)
    {
        $html = "<div class=\"orderAttribute\"><div class=\"label_attribute\">
            <span>" . $attribute->getFrontendLabel() . ': ' .
            "</span></div>" . "<div class=\"value_attribute\"><audio controls " . $noDownload . " >" .
            "<source src=\"" . $this->getViewFile($valueAttribute) . "\" type=\"audio/mpeg\">
            <source src=\"" . $this->getViewFile($valueAttribute) . "\" type=\"audio/ogg\">
            Your browser does not support the audio element.
            </audio>
            </div>" . "</div><br/>";

        return $html;
    }

    /**
     * @param Attribute $attribute
     * @param string $valueAttribute
     * @param string $noDownloadFile
     * @return string
     */
    private function getFileOtherFrontend($attribute, $valueAttribute, $noDownloadFile)
    {
        $html = "<div class=\"orderAttribute\"><div class=\"label_attribute\">
            <span>" . $attribute->getFrontendLabel() . ': ' . "</span></div>" .
            "<div class=\"value_attribute\"><span>" .
            "<a href=\"" . $this->getViewFile($valueAttribute) . "\"" . " " . $noDownloadFile . " " .
            "target=\"_blank\">" . $this->getFileName($valueAttribute) . "</a>
            </span></div></div><br/>";

        return $html;
    }

    /**
     * Return escaped value
     *
     * @param $fieldValue
     * @param $entityType
     * @return string
     */
    public function getViewFile($fieldValue, $entityType = 'customer')
    {
        if ($fieldValue) {
            return $this->_getUrl(
                'customerattribute/index/viewfile',
                [
                    'file' => $this->urlEncoder->encode($fieldValue),
                    'path' => $entityType
                ]
            );
        }
        return $fieldValue;
    }

    /**
     * @param Attribute $attribute
     * @return mixed
     */
    public function getValueValidateFile($attribute)
    {
        $value = $attribute->getData('validate_rules');
        // @codingStandardsIgnoreStart
        return $this->json->unserialize($value);
    }

    /**
     * Get Default Value Required
     *
     * @param \Magento\Customer\Model\Attribute $attributeObject
     * @return mixed|string
     */
    public function getDefaultValueRequired($attributeObject)
    {
        $frontendInput = $attributeObject->getFrontendInput();
        $defaultRequired = "";
        if ($frontendInput == 'text'
            || $frontendInput == "textarea"
            || $frontendInput == "date"
            || $frontendInput == "file"
        ) {
            $validateRules = $attributeObject->getValidateRules();
            if ($validateRules) {
                if (!is_array($validateRules)) {
                    $validateRules = json_decode($validateRules, true);
                }
                if (isset($validateRules['default_value_required'])) {
                    $defaultRequired = $validateRules['default_value_required'];
                }
            }
        } else {
            $defaultRequired = $attributeObject->getDefaultValue();
        }

        return $defaultRequired;
    }

    /**
     * @param string $filename
     * @return string
     */
    public function getFileName($filename)
    {
        if ($filename && strpos($filename, "/") !== false) {
            $nameArr = explode("/", $filename);
            return end($nameArr);
        }
        return $filename;
    }

    /**
     * Get url to upload file in checkout page
     *
     * @return string
     */
    public function getUrlUploadFile()
    {
        return $this->_urlBuilder->getUrl("customerattribute/file/uploader/");
    }

    /**
     * Get url to upload file in checkout page
     *
     * @return string
     */
    public function getUrlUploadFileAdress()
    {
        return $this->_urlBuilder->getUrl("customer/address_file/upload");
    }

    /**
     * Get max file size server php
     *
     * @return float
     */
    public function getMaxFileSizePhp()
    {
        return $this->fileSize->getMaxFileSizeInMb() * 1024;
    }

    /**
     * Get data customer attribute
     *
     * @param Attribute $attribute
     * @return array
     */
    public function getDataCustomerAttributes($attribute)
    {
        $validateInputFile = $this->getValueValidateFile($attribute);
        $fileSize = $this->getMaxFileSizePhp();
        $fileExtension = "bss_nothing";
        if (isset($validateInputFile['max_file_size']) && $fileSize > $validateInputFile['max_file_size']) {
            $fileSize = $validateInputFile['max_file_size'];
        }
        if (isset($validateInputFile['file_extensions'])) {
            $fileExtension = $validateInputFile['file_extensions'];
        }
        $validatorSize = $attribute->getAttributeCode() . "size";
        $validatorExtensions = $attribute->getAttributeCode() . "extension";
        return [
            "fileSize" => $fileSize,
            "fileExtension" => $fileExtension,
            "validatorSize" => $validatorSize,
            "validatorExtensions" => $validatorExtensions
        ];
    }

    /**
     * Check customer is B2B
     *
     * @return bool
     */
    public function checkCustomerB2B($customerId = null)
    {
        $customerB2b = [self::B2B_PENDING, self::B2B_APPROVAL, self::B2B_REJECT];
        if (!$this->checkCustomerB2B) {
            if (!$customerId) {
                $customer = $this->customerSession->getCustomer();
                if ($customer && $customer->getData()) {
                    if (in_array($customer->getData("b2b_activasion_status"), $customerB2b)) {
                        $this->checkCustomerB2B = true;
                    }
                } else {
                    $this->checkCustomerB2B = false;
                }
            } else {
                $customer = $this->customerRepository->getById($customerId);
                if ($customer && $customer->getCustomAttribute('b2b_activasion_status') !== null) {
                    if ((int)in_array($customer->getCustomAttribute('b2b_activasion_status')->getValue(), $customerB2b)) {
                        $this->checkCustomerB2B = true;
                    }
                } else {
                    $this->checkCustomerB2B = false;
                }
            }
        }
        return $this->checkCustomerB2B;
    }

    /**
     * Is active attribute
     *
     * @param \Magento\Eav\Model\Attribute $attribute
     * @return bool
     */
    public function isActiveAttribute($attribute)
    {
        if ($this->isEnable() && $attribute->getIsVisible()) {
            return true;
        }
        return false;
    }

    /**
     * Is active attribute
     *
     * @param \Magento\Eav\Model\Attribute $attribute
     * @return bool
     */
    public function isDisableAttributeAddress($attribute)
    {
        $addressCollection = $this->converAddressCollectioin(0);
        $attributeCode = $attribute->getAttributeCode();
        if (isset($addressCollection[$attributeCode])) {
            if (!$addressCollection[$attributeCode]->getIsVisible() || !$this->isEnable()) {
                return true;
            }
        }
        return false;
    }

    /**
     * check attribute display in
     *
     * @param string $attributeCode
     * @throws LocalizedException
     */
    public function getAttributeDisplayIn($attributeCode)
    {
        $attribute = $this->eavAttribute->create()
            ->getAttribute('customer', $attributeCode);
        $usedInForms = $attribute->getUsedInForms();
        foreach ($usedInForms as $value) {
            return is_numeric($value) ? $value : '';
        }
        return '';
    }

    /**
     * Check attr will display depend on type account
     *
     * @param string $attributeCode
     * @param int $customerId
     * @return bool
     * @throws LocalizedException
     */
    public function checkDisplayInB2bOrNormalAccount($attributeCode, $customerId = null)
    {
        if ($this->checkCustomerB2B($customerId) &&
            $this->getAttributeDisplayIn($attributeCode)
            == DisplayBackendCustomerDetail::NORMAL_ACCOUNTS) {
            return false;
        }
        if (!$this->checkCustomerB2B($customerId) &&
            $this->getAttributeDisplayIn($attributeCode)
            == DisplayBackendCustomerDetail::B2B_ACCOUNTS) {
            return false;
        }
        return true;
    }
}
