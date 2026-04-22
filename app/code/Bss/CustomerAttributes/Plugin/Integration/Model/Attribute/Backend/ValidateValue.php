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
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomerAttributes\Plugin\Integration\Model\Attribute\Backend;

use Bss\CustomerAttributes\Helper\Customerattribute;
use Bss\CustomerAttributes\Model\Config\Source\DisplayBackendCustomerDetail;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Module\Manager;

/**
 * Class ValidateValue
 * @package Bss\CustomerAttributes\Plugin\Integration\Model\Attribute\Backend
 */
class ValidateValue extends \Bss\CustomerAttributes\Plugin\Model\Attribute\Backend\ValidateValue
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $area;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Eav\Model\ConfigFactory
     */
    protected $eavAttribute;

    /**
     * @var \Bss\CustomerAttributes\Helper\B2BRegistrationIntegrationHelper
     */
    private $b2BRegistrationIntegration;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * ValidateValue constructor.
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\State $area
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Eav\Model\ConfigFactory $eavAttributeFactory
     * @param \Bss\CustomerAttributes\Helper\B2BRegistrationIntegrationHelper $b2BRegistrationIntegration
     * @param \Bss\CustomerAttributes\Helper\Customerattribute $customerattribute
     * @param Manager $moduleManager
     * @param Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\State $area,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Eav\Model\ConfigFactory $eavAttributeFactory,
        \Bss\CustomerAttributes\Helper\B2BRegistrationIntegrationHelper $b2BRegistrationIntegration,
        \Bss\CustomerAttributes\Helper\Customerattribute $customerattribute,
        Manager $moduleManager,
        Session $customerSession
    ) {
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->area = $area;
        $this->registry = $registry;
        $this->customerRepository = $customerRepository;
        $this->eavAttribute = $eavAttributeFactory;
        $this->b2BRegistrationIntegration = $b2BRegistrationIntegration;
        $this->moduleManager = $moduleManager;
        $this->customerSession = $customerSession;
        parent::__construct($customerattribute);
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param $object
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function aroundValidate(
        $subject,
        callable $proceed,
        $object
    ) {
        if ($this->moduleManager->isEnabled('Bss_B2bRegistration') && !$this->isCreateNewAccountBackEnd()) {
            $customerB2b = [
                Customerattribute::B2B_PENDING,
                Customerattribute::B2B_APPROVAL,
                Customerattribute::B2B_REJECT
            ];
            $customerId = $this->request->getParam('customer_id');
            if (!$customerId) {
                $customerId = $this->customerSession->getCustomerId();
            }
            $customer = $this->customerRepository->getById($customerId);
            $attribute = $this->eavAttribute->create()
                ->getAttribute('customer', $subject->getAttribute()->getAttributeCode());
            if ($customer && $customer->getCustomAttribute('b2b_activasion_status') !== null) {
                if (in_array((int)$customer->getCustomAttribute('b2b_activasion_status')->getValue(), $customerB2b)) {
                    if ($attribute) {
                        $usedInForms = $attribute->getUsedInForms();

                        if (in_array('is_customer_attribute', $usedInForms) && $attribute->getIsRequired() &&
                            $this->b2BRegistrationIntegration->getAttributeDisplay($attribute->getAttributeCode()) ==
                            DisplayBackendCustomerDetail::NORMAL_ACCOUNTS) {
                            return true;
                        }
                    }
                } else {
                    if ($attribute) {
                        $usedInForms = $attribute->getUsedInForms();
                        if (in_array('is_customer_attribute', $usedInForms) && $attribute->getIsRequired() &&
                            $this->b2BRegistrationIntegration->getAttributeDisplay($attribute->getAttributeCode()) ==
                            DisplayBackendCustomerDetail::B2B_ACCOUNTS) {
                            return true;
                        }
                    }
                }
            } else {
                if ($attribute) {
                    $usedInForms = $attribute->getUsedInForms();
                    if (in_array('is_customer_attribute', $usedInForms) && $attribute->getIsRequired() &&
                        $this->b2BRegistrationIntegration->getAttributeDisplay($attribute->getAttributeCode()) ==
                        DisplayBackendCustomerDetail::B2B_ACCOUNTS) {
                        return true;
                    }
                }
            }

            return $proceed($object);
        } else {
            if ($this->request->getControllerName() == 'order_create') {
                return true;
            }
        }

        return parent::aroundValidate($subject, $proceed, $object);
    }

    /**
     * Check is create new account
     *
     * @return bool
     */
    public function isCreateNewAccountBackEnd()
    {
        if (!$this->customerSession->getCustomerId()
            && !$this->request->getParam('customer_id')
        ) {
            return true;
        } else {
            return false;
        }
    }
}
