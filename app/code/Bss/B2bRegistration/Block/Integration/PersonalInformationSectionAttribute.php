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
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\B2bRegistration\Block\Integration;

use Bss\B2bRegistration\Helper\ModuleIntegration;
use Magento\Eav\Model\ConfigFactory;

/**
 * Class PersonalInformationSectionAttribute
 *
 * @package Bss\CustomerAttributes\Block\Integration
 */
class PersonalInformationSectionAttribute extends \Magento\Customer\Block\Form\Register
{
    /**
     * @var ConfigFactory
     */
    private $eavAttribute;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $session;

    /**
     * @var ModuleIntegration
     */
    private $moduleIntegration;

    /**
     * PersonalInformationSectionAttribute constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param ConfigFactory $eavAttribute
     * @param ModuleIntegration $moduleIntegration
     * @param \Magento\Checkout\Model\Session $session
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Url $customerUrl,
        ConfigFactory $eavAttribute,
        ModuleIntegration $moduleIntegration,
        \Magento\Checkout\Model\Session $session,
        array $data = []
    ) {
        $this->eavAttribute = $eavAttribute;
        $this->session = $session;
        $this->moduleIntegration = $moduleIntegration;
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
     * @return $this|\Magento\Customer\Block\Form\Register
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->moduleIntegration->isBssCustomerAttributesModuleEnabled()) {
            $this->setTemplate('Bss_CustomerAttributes::form/register/customer_attribute.phtml');
        }
        return $this;
    }

    /**
     * Check if attribute available show here
     *
     * @param string|int $attributeCode
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isShowIn($attributeCode)
    {
        $attribute = $this->eavAttribute->create()
            ->getAttribute('customer', $attributeCode);
        $usedInForms = $attribute->getUsedInForms();

        if (in_array('personal_infor_section', $usedInForms) && in_array('b2b_account_create', $usedInForms)) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getCustomerAttributeHelper()
    {
        return $this->moduleIntegration->getBssCustomerAttributeHelper();
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    public function getSession()
    {
        return $this->session;
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
}
