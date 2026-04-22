<?php
declare(strict_types=1);

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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Model;

use Bss\CompanyAccount\Exception\B2bRegistrationStatusException;
use Bss\CompanyAccount\Helper\Data;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Validate customer with module b2b registration
 */
class B2bRegistrationStatusValidator
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * B2bRegistrationStatusValidator constructor.
     *
     * @param Data $helper
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        Data $helper,
        CustomerFactory $customerFactory
    ) {
        $this->helper = $helper;
        $this->customerFactory = $customerFactory;
    }

    /**
     * Validate
     *
     * @throws B2bRegistrationStatusException
     */
    public function validate($customer)
    {
        try {
            $result = $this->validateCustomer($customer);

            if ($result) {
                throw new B2bRegistrationStatusException($result);
            }
        } catch (NoSuchEntityException $e) {
            throw new B2bRegistrationStatusException(__($e->getMessage()));
        }
    }

    /**
     * @param CustomerInterface $customer
     * @return false|\Magento\Framework\Phrase
     * @throws NoSuchEntityException
     */
    public function validateCustomer($customer)
    {
        if (!$customer) {
            return __("Customer not found");
        }

        if ($this->helper->isModuleOutputEnabled('Bss_B2bRegistration')) {
            if ($this->helper->getScopeConfig()->isSetFlag(
                Data::XML_PATH_B2BREGISTRATION_ENABLE_CONFIG,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $this->helper->getStoreManager()->getStore()->getId()
            )
            ) {
                $customerAttr = $customer->getCustomAttribute('b2b_activasion_status');
                if ($customerAttr) {
                    $customerAttrValue = $customerAttr->getValue();
                    if ($customerAttrValue ==
                        \Bss\B2bRegistration\Model\Config\Source\CustomerAttribute::B2B_PENDING
                    ) {
                        return __('The associated company account is not verified. Please try again later.');
                    } elseif ($customerAttrValue ==
                        \Bss\B2bRegistration\Model\Config\Source\CustomerAttribute::B2B_REJECT
                    ) {
                        return __(
                            'The associated company account is inactive. ' .
                            'Please contact your company account for more information.'
                        );
                    }
                }
            }
        }

        if (!$this->helper->isCompanyAccount($customer)) {
            return __('You can not access here any more. For more detail, contact your company account.');
        }

        return false;
    }
}
