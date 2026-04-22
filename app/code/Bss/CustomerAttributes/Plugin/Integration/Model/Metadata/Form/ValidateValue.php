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
 * @copyright  Copyright (c) 2020-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Plugin\Integration\Model\Metadata\Form;

use Bss\CustomerAttributes\Helper\Customerattribute;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Eav\Model\ConfigFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Module\Manager;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ValidateValue
 * @SuppressWarnings(PHPMD)
 * @package Bss\CustomerAttributes\Plugin\Model\Metadata\Form
 */
class ValidateValue extends \Bss\CustomerAttributes\Plugin\Model\Metadata\Form\ValidateValue
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var State
     */
    protected $area;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var ConfigFactory
     */
    protected $eavAttribute;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * ValidateValue constructor.
     * @param Http $request
     * @param Customerattribute $customerAttribute
     * @param ScopeConfigInterface $scopeConfig
     * @param State $area
     * @param Registry $registry
     * @param CustomerRepositoryInterface $customerRepository
     * @param ConfigFactory $eavAttributeFactory
     * @param Manager $moduleManager
     */
    public function __construct(
        Http                             $request,
        Customerattribute                $customerAttribute,
        ScopeConfigInterface             $scopeConfig,
        State                            $area,
        Registry                         $registry,
        CustomerRepositoryInterface      $customerRepository,
        ConfigFactory                    $eavAttributeFactory,
        Manager $moduleManager
    ) {
        parent::__construct(
            $request,
            $customerAttribute,
            $eavAttributeFactory
        );
        $this->eavAttribute = $eavAttributeFactory;
        $this->scopeConfig = $scopeConfig;
        $this->area = $area;
        $this->registry = $registry;
        $this->customerRepository = $customerRepository;
        $this->moduleManager = $moduleManager;
    }

    /**
     * Don't validate the required address attributes when creating B2B account...
     *
     * @param mixed $subject
     * @param array|bool $result
     * @return array|bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterValidateValue(
        $subject,
        $result
    ) {
        if ($result === true) { // Returns true immediately to optimize output performance
            return true;
        }

        if ($this->customerAttribute->isDisableAttributeAddress($subject->getAttribute())) {
            return true;
        }

        if ($this->moduleManager->isEnabled('Bss_B2bRegistration')) {
            $attributeCode = $subject->getAttribute()->getAttributeCode();
            $page = $this->request->getFullActionName();
            if ($this->DontValidateRequireAddress($attributeCode)) {
                return true;
            }
            $attribute = $this->eavAttribute->create()
                ->getAttribute('customer', $attributeCode);
            if ($attribute) {
                $usedInForms = $attribute->getUsedInForms();
                $enableCustomerAttribute = $this->scopeConfig->getValue(
                    'bss_customer_attribute/general/enable',
                    ScopeInterface::SCOPE_STORE
                );

                if (in_array('is_customer_attribute', $usedInForms) && $attribute->getIsRequired()) {
                    $newB2bValue = "";
                    /* Backend Validate */
                    if ($this->area->getAreaCode() == "adminhtml") {
                        $customer = $this->registry->registry('bss_customer');
                        if ($customer->getId()) {
                            $customerId = $customer->getId();
                            $oldData = $this->customerRepository->getById($customerId);
                            $oldB2b = $oldData->getCustomAttribute('b2b_activasion_status');
                            $oldB2bValue = $oldB2b ? $oldB2b->getValue() : "";
                            $newB2bValue = $customer->getCustomAttribute('b2b_activasion_status')->getValue();
                            if ((!$oldB2bValue || !$newB2bValue) && ($oldB2bValue != $newB2bValue)) {
                                return true;
                            }
                        }
                        if ($newB2bValue) {
                            /* B2b account */
                            if (!in_array('b2b_account_create', $usedInForms)) {
                                return true;
                            }
                        } else {
                            /* Normal account */
                            if (!in_array('customer_account_create_frontend', $usedInForms)) {
                                return true;
                            }
                        }
                    }


                    if ((!in_array('b2b_account_create', $usedInForms)
                            || !$enableCustomerAttribute) && $page == 'btwob_account_createpost') {
                        return true;
                    }
                    if ((!in_array('b2b_account_edit', $usedInForms)
                            || !$enableCustomerAttribute) && $page == 'customer_account_editPost') {
                        return true;
                    }
                    if (!in_array('b2b_account_create', $usedInForms)
                        || !in_array('customer_account_create_frontend', $usedInForms)) {
                        return true;
                    }
                }
            }

            return $result;
        }

        return parent::afterValidateValue($subject, $result);
    }

    /**
     * Don't validate the required address attributes when creating B2B account.
     *
     * @param string $attributeCode
     * @return bool
     */
    public function DontValidateRequireAddress($attributeCode): bool
    {
        if ($this->request->getFullActionName() == "btwob_account_createpost") {
            $addressCollection = $this->customerAttribute->getAddressCollection();
            if ($addressCollection->getSize()) {
                foreach ($addressCollection as $address) {
                    if ($address->getAttributeCode() == $attributeCode) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}
