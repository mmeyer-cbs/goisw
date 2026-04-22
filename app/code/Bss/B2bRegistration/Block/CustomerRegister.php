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
namespace Bss\B2bRegistration\Block;

use Bss\B2bRegistration\Helper\Data;
use Bss\B2bRegistration\Helper\ModuleIntegration;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class CustomerRegister
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerRegister extends \Magento\Customer\Block\Form\Register
{
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $context;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Framework\App\Cache\Type\Config
     */
    protected $configCacheType;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory
     */
    protected $regionCollectionFactory;
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $countryCollectionFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * @var Address
     */
    protected $address;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $helperDirectoryData;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerInterface;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var ModuleIntegration
     */
    protected $moduleIntegration;

    /**
     * CustomerRegister constructor.
     * @param Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param EncoderInterface $jsonEncoder
     * @param Config $configCacheType
     * @param CollectionFactory $regionCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param Manager $moduleManager
     * @param Session $customerSession
     * @param Url $customerUrl
     * @param \Magento\Directory\Helper\Data $helperDirectoryData
     * @param Data $helper
     * @param Address $address
     * @param ModuleIntegration $moduleIntegration
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Directory\Helper\Data $helperDirectoryData,
        Data $helper,
        Address $address,
        ModuleIntegration $moduleIntegration,
        array $data = []
    ) {
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
        $this->helperDirectoryData = $helperDirectoryData;
        $this->helper = $helper;
        $this->address = $address;
        $this->escaper = $context->getEscaper();
        $this->moduleIntegration=$moduleIntegration;
        $this->storeManagerInterface = $context->getStoreManager();
    }

    /**
     *  Get Title Of B2b Account Create Page
     * @return void
     */
    protected function _prepareLayout()
    {
        $title = $this->helper->getTitle();
        if ($title) {
            $this->pageConfig->getTitle()->set(__($title));
        } else {
            $this->pageConfig->getTitle()->set(__('Create New Customer Account'));
        }
    }

    /**
     * Count Stress
     * @return int
     */
    public function getStressCount()
    {
        return $this->address->getStreetLines();
    }

    /**
     * Enable Date Field
     *
     * @return bool
     */
    public function isEnableDateField()
    {
        return $this->helper->isEnableDateField();
    }

    /**
     * Enable Tax Field
     * @return bool
     */
    public function isEnableTaxField()
    {
        return $this->helper->isEnableTaxField();
    }

    /**
     * Enable Gender Field
     * @return bool
     */
    public function isEnableGenderField()
    {
        return $this->helper->isEnableGenderField();
    }

    /**
     * Enable Address Field
     * @return bool
     */
    public function isEnableAddressField()
    {
        return $this->helper->isEnableAddressField();
    }

    /**
     * Get Prefix Field
     * @return bool
     */
    public function isEnablePrefixField()
    {
        return $this->helper->isEnablePrefixField();
    }

    /**
     * Get Prefix Options
     * @return array|bool
     */
    public function getPrefixOptions()
    {
        return $this->prepareNamePrefixSuffixOptions($this->helper->getPrefixOptions());
    }

    /**
     * Get Suffix Field
     * @return bool
     */
    public function isEnableSuffixField()
    {
        return $this->helper->isEnableSuffixField();
    }

    /**
     * Get Suffix Field Options
     * @return array|bool
     */
    public function getSuffixOptions()
    {
        return $this->prepareNamePrefixSuffixOptions($this->helper->getSuffixOptions());
    }

    /**
     * Get enable Middle Field
     * @return bool
     */
    public function isEnableMiddleField()
    {
        return $this->helper->isEnableMiddleField();
    }

    /**
     * Convert String To arrays
     * @param string $options
     * @return array|bool
     */
    public function prepareNamePrefixSuffixOptions($options)
    {
        if (!$options) {
            return false;
        }
        $options = trim($options);
        $result = [];
        $options = explode(';', $options);
        foreach ($options as $value) {
            $value = $this->escaper->escapeHtml(trim($value));
            $result[$value] = $value;
        }
        return $result;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPostAction()
    {
        $baseUrl = $this->storeManagerInterface->getStore()->getBaseUrl();
        $postAction = $baseUrl . 'btwob/account/createpost';
        return $postAction;
    }

    /**
     * Get Directory Helper Data
     * @return \Magento\Directory\Helper\Data
     */
    public function getHelperDirectoryData()
    {
        return $this->helperDirectoryData;
    }

    /**
     * Get Suffix Field Default Config
     * @return string
     */
    public function getSuffixFieldDefault()
    {
        return $this->helper->getSuffixFieldDefault();
    }

    /**
     * Get Prefix Field Default Config
     * @return string
     */
    public function getPreffixFieldDefault()
    {
        return $this->helper->getPreffixFieldDefault();
    }

    /**
     * Get Dob Field Default Config
     * @return string
     */
    public function getDobFieldDefault()
    {
        return $this->helper->getDobFieldDefault();
    }

    /**
     * Get Tax Field Default Config
     * @return string
     */
    public function getTaxFieldDefault()
    {
        return $this->helper->getTaxFieldDefault();
    }

    /**
     * Get Gender Field Default Config
     * @return string
     */
    public function getGenderFieldDefault()
    {
        return $this->helper->getGenderFieldDefault();
    }

    /**
     * Get Telephone Field Default Config
     * @return string
     */
    public function getTelephoneFieldDefault()
    {
        return $this->helper->getTelephoneFieldDefault();
    }

    /**
     * Get Company Field Default Config
     * @return string
     */
    public function getCompanyFieldDefault()
    {
        return $this->helper->getCompanyFieldDefault();
    }

    /**
     * Get Fax Field Default Config
     * @return string
     */
    public function getFaxFieldDefault()
    {
        return $this->helper->getFaxFieldDefault();
    }

    /**
     * Get Vat Field Default Config
     * @return string
     */
    public function getVatFieldDefault()
    {
        return $this->helper->getVatFieldDefault();
    }

    /**
     * Compare Magento Version
     * @return bool
     */
    public function hasMagentoVersion()
    {
        return $this->moduleIntegration->hasMagentoVersion();
    }

    /**
     * Is Enable Company Account
     *
     * @return mixed
     */
    public function isEnableCompanyAccount()
    {
        return $this->helper->isEnableCompanyAccount();
    }

    /**
     * Get Company Account
     *
     * @return mixed
     */
    public function getCompanyAccount()
    {
        return $this->helper->getCompanyAccount();
    }
}
