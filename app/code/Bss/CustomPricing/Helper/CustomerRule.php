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
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomPricing\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Bss\CustomPricing\Api\PriceRuleRepositoryInterface;

/**
 * Helper data for module
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class CustomerRule extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Bss\CustomPricing\Model\AppliedCustomers
     */
    protected $customerRule;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Bss\CustomPricing\Model\ResourceModel\Indexer\BaseFinalPrice
     */
    protected $indexPrice;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var PriceRuleRepositoryInterface
     */
    protected $priceRuleRepository;

    /**
     * CustomerRule constructor.
     * @param Context $context
     * @param \Bss\CustomPricing\Model\AppliedCustomers $customerRule
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     * @param \Bss\CustomPricing\Model\ResourceModel\Indexer\BaseFinalPrice $indexPrice
     * @param StoreManagerInterface $storeManager
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param PriceRuleRepositoryInterface $priceRuleRepository
     */
    public function __construct(
        Context $context,
        \Bss\CustomPricing\Model\AppliedCustomers $customerRule,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Bss\CustomPricing\Model\ResourceModel\Indexer\BaseFinalPrice $indexPrice,
        StoreManagerInterface $storeManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        PriceRuleRepositoryInterface $priceRuleRepository
    ) {
        $this->customerRule = $customerRule;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->customerSession = $customerSession;
        $this->indexPrice = $indexPrice;
        $this->storeManager = $storeManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->priceRuleRepository = $priceRuleRepository;
        parent::__construct($context);
    }

    /**
     * Get special rule by customer id
     *
     * @param null|int $customerId
     * @return false|string
     */
    public function getSpecialRuleByCustomerId($customerId = null)
    {
        if (!$customerId) {
            $customerId = $this->customerSessionFactory->create()->getCustomerId();
        }
        $storeId = $this->storeManager->getStore()->getId();
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        return $this->customerRule->getCustomerRule($customerId, $websiteId);
    }

    /**
     * Get special rule for not logged in Customer
     *
     * @return false|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSpecialRuleNotLoggedIn()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        $this->searchCriteriaBuilder->addFilter('is_not_logged_rule', true);
        $this->searchCriteriaBuilder->addFilter('website_id', $websiteId);
        $this->searchCriteriaBuilder->addFilter('status', 1);
        $rules = $this->priceRuleRepository->getList(
            $this->searchCriteriaBuilder->create()
        )->getItems();
        if (!empty($rules) && is_array($rules)) {
            $ruleids = array_keys($rules);
            return (implode("-", $ruleids));
        }
        return false;
    }

    /**
     * Set session rule to customer session
     *
     * @param string $ruleIds
     * @return mixed
     * @deprecated v1.0.3
     */
    public function setSessionRule($ruleIds)
    {
        return $this->customerSession->setBssCustomRule($ruleIds);
    }

    /**
     * Get applied rules for current customer
     *
     * @param string|null $customerId
     * @param bool $checkDb - check with db data (need for check cache id)
     * @return string
     */
    public function getAppliedRules(string $customerId = null, bool $checkDb = true): ?string
    {
        if ($customerId === null) {
            $customerId = $this->getCustomerSession()->getCustomerId();
            if (!$customerId) {
                $customerId = "0";
            }
        }

        $appliedRules = $this->getCustomerSession()->getAppliedRules();

        if (isset($appliedRules[$customerId])) {
            if ($checkDb) {
                // Check applied rule customer was changed
                $dbRuleIds = $this->getCustomerRuleIds($customerId);
                if ($dbRuleIds !== $appliedRules[$customerId]) {
                    return null;
                }
            }

            return $appliedRules[$customerId];
        }

        return null;
    }

    /**
     * Get rules were applied to customer
     *
     * @param string $customerId - Customer id, "0" is NOT LOGIN CUSTOMER
     * @return string
     */
    public function getCustomerRuleIds(string $customerId = "0"): ?string
    {
        if ($customerId !== '0') {
            $ids = $this->getSpecialRuleByCustomerId();
        } else {
            $ids = $this->getSpecialRuleNotLoggedIn();
        }

        if ($ids) {
            return $ids;
        }

        return null;
    }

    /**
     * Set applied rules for current customer or guest
     *
     * @param string|null $customerId
     * @param array|null $ruleIds
     * @return string
     */
    public function setAppliedRules(string $customerId = null, array $ruleIds = null): ?string
    {
        if ($customerId === null) {
            $customerId = $this->getCustomerSession()->getCustomerId();
            if (!$customerId) {
                $customerId = "0";
            }
        }

        if (!$ruleIds) {
            $ruleIds = $this->getCustomerRuleIds($customerId);
        }

        if (!$ruleIds) {
            return null;
        }

        // convert to string
        if (is_array($ruleIds)) {
            $ruleIds = implode("-", $ruleIds);
        }

        $this->getCustomerSession()->setAppliedRules(
            [$customerId => $ruleIds]
        );

        return $ruleIds;
    }
    /**
     * Get customer session
     *
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->customerSessionFactory->create();
    }

    /**
     * Is have custom price
     *
     * @param string $ruleIds
     * @param int $productId
     * @param int $customerGroup
     * @return bool
     */
    public function isAppliedCustomPrice($ruleIds, $productId, $customerGroup)
    {
        $inforPrices = $this->indexPrice->getPriceFromIndex($ruleIds, $productId, $customerGroup);
        if (is_array($inforPrices) && !empty($inforPrices)) {
            return true;
        }
        return false;
    }
}
