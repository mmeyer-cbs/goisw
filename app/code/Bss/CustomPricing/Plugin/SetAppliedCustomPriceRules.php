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
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomPricing\Plugin;

use Magento\Customer\Api\Data\CustomerInterface as CustomerData;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Http\Context as HttpContext;

/**
 * Class SetAppliedCustomPriceRules
 * Set applied custom price rule for context vary data for cache identifier
 */
class SetAppliedCustomPriceRules
{
    /**
     * @var \Bss\CustomPricing\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Bss\CustomPricing\Helper\CustomerRule
     */
    protected $helperRule;

    /**
     * @var HttpContext
     */
    protected $httpContext;

    /**
     * SetAppliedCustomPriceRules constructor.
     *
     * @param \Bss\CustomPricing\Helper\Data $helperData
     * @param \Bss\CustomPricing\Helper\CustomerRule $helperRule
     * @param HttpContext $httpContext
     */
    public function __construct(
        \Bss\CustomPricing\Helper\Data $helperData,
        \Bss\CustomPricing\Helper\CustomerRule $helperRule,
        HttpContext $httpContext
    ) {
        $this->helperData = $helperData;
        $this->helperRule = $helperRule;
        $this->httpContext = $httpContext;
    }

    /**
     * Set applied custom price rule for context vary data for cache identifier
     *
     * @param ActionInterface $subject
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(UnusedFormalParameter)
     */
    public function beforeExecute(
        ActionInterface $subject
    ) {
        if (!$this->helperData->isEnabled()) {
            return;
        }

        $this->setAppliedRulesToHttpContext();
    }

    /**
     * Check and set applied rules to http context for new customer login
     *
     * @param CustomerSession $subject
     * @param CustomerSession $result
     * @param CustomerData $customer
     * @return CustomerSession
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(UnusedFormalParameter)
     */
    public function afterSetCustomerData(
        CustomerSession $subject,
        CustomerSession $result,
        CustomerData $customer
    ) {
        if (!$this->helperData->isEnabled() || $customer === null) {
            return $result;
        }

        $this->setAppliedRulesToHttpContext($customer->getId());

        return $result;
    }

    /**
     * Check and set applied rules to http context
     *
     * @param string|null $customerId
     */
    protected function setAppliedRulesToHttpContext(string $customerId = null)
    {
        // Get existed applied rules with current customer
        // if it does not exist or is not correct for the current customer then try to process set
        $rules = $this->helperRule->getAppliedRules($customerId);

        if (!$rules) {
            $rules = $this->helperRule->setAppliedRules($customerId);
        }

        // New rules
        if ($rules) {
            $this->httpContext->setValue("APPLIED_RULES_IDS", $rules, "");
        }
    }
}
