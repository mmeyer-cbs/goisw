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

namespace Bss\CustomerAttributes\Plugin\Integration\Model\ResourceModel;

use Bss\CustomerAttributes\Helper\B2BRegistrationIntegrationHelper;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Module\Manager;

/**
 * Class CustomerRepository
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @package Bss\CustomerAttributes\Plugin\Model\ResourceModel
 */
class CustomerRepository
{
    /**
     * @var Registry $registry
     */
    protected $registry;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * @var B2BRegistrationIntegrationHelper
     */
    private $b2BRegistrationIntegration;

    /**
     * CustomerRepository constructor.
     * @param Registry $registry
     * @param B2BRegistrationIntegrationHelper $b2BRegistrationIntegration
     * @param Manager $moduleManager
     */
    public function __construct(
        Registry                         $registry,
        B2BRegistrationIntegrationHelper $b2BRegistrationIntegration,
        Manager $moduleManager
    )
    {
        $this->registry = $registry;
        $this->b2BRegistrationIntegration = $b2BRegistrationIntegration;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param CustomerInterface $customer
     * @param null $passwordHash
     * @return mixed
     */
    public function aroundSave(
        $subject,
        callable $proceed,
        CustomerInterface $customer,
        $passwordHash = null
    ) {
        if ($this->moduleManager->isEnabled('Bss_B2bRegistration')) {
            if ($this->registry->registry('bss_customer')) {
                $this->registry->unregister('bss_customer');
                $this->registry->register('bss_customer', $customer);
            } else {
                $this->registry->register('bss_customer', $customer);
            }
        }

        return $proceed($customer, $passwordHash);
    }
}
