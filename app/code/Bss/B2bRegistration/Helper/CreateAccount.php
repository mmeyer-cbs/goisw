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
namespace Bss\B2bRegistration\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Customer\Model\SessionFactory;
use Magento\Newsletter\Model\SubscriberFactory;

/**
 * Class CreateAccount
 *
 * @package Bss\B2bRegistration\Helper
 */
class CreateAccount
{
    protected $context;
    /**
     * @var SessionFactory
     */
    protected $customerSession;

    /**
     * @var FormFactory
     */
    protected $formFactory;

    /**
     * @var SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * @var RegionInterfaceFactory
     */
    protected $regionDataFactory;

    /**
     * @var Address
     */
    protected $addressHelper;

    /**
     * @var AddressInterfaceFactory
     */
    protected $addressDataFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * CreateAccount constructor.
     * @param SessionFactory $customerSession
     * @param FormFactory $formFactory
     * @param SubscriberFactory $subscriberFactory
     * @param RegionInterfaceFactory $regionDataFactory
     * @param Address $addressHelper
     * @param AddressInterfaceFactory $addressDataFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        SessionFactory $customerSession,
        FormFactory $formFactory,
        SubscriberFactory $subscriberFactory,
        RegionInterfaceFactory $regionDataFactory,
        Address $addressHelper,
        AddressInterfaceFactory $addressDataFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->customerSession = $customerSession;
        $this->formFactory = $formFactory;
        $this->subscriberFactory = $subscriberFactory;
        $this->regionDataFactory = $regionDataFactory;
        $this->addressHelper = $addressHelper;
        $this->addressDataFactory = $addressDataFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return SessionFactory
     */
    public function getCustomerSessionFactory()
    {
        return $this->customerSession;
    }

    /**
     * @return ScopeConfigInterface
     */
    public function getScopeConfig()
    {
        return $this->scopeConfig;
    }

    /**
     * @return FormFactory
     */
    public function getFormFactory()
    {
        return $this->formFactory;
    }

    /**
     * @return \Magento\Newsletter\Model\Subscriber
     */
    public function getSubscriberFactory()
    {
        return $this->subscriberFactory->create();
    }

    /**
     * @return \Magento\Customer\Api\Data\RegionInterface
     */
    public function getRegionDataFactory()
    {
        return $this->regionDataFactory->create();
    }

    /**
     * @return Address
     */
    public function getAddressHelper()
    {
        return $this->addressHelper;
    }

    /**
     * @return \Magento\Customer\Api\Data\AddressInterface
     */
    public function getDataAddressFactory()
    {
        return $this->addressDataFactory->create();
    }
}
