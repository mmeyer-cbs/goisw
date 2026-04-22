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
 * @package    Bss_OrderAmount
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OrderAmount\Helper;

/**
 * Class Data
 *
 * @package Bss\OrderAmount\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const BSS_ENABLE = 'sales/minimum_order/active';
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->serializer = $serializer;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->storeManager = $storeManager;
    }

    /**
     * Customer login data
     */
    public function getCustomer()
    {
        if (empty($this->customerSession->getCustomer()->getData())) {
            return null;
        } else {
            $customerId = $this->customerSession->getCustomer()->getId();
            return $this->customerRepositoryInterface->getById($customerId);
        }
    }

    /**
     *
     * @param mixed $store
     * @return bool|mixed
     */
    public function getAmountData($store = null)
    {
        $amountData = $this->scopeConfig->getValue(
            'sales/minimum_order/amount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );

        try {
            return $this->serializer->unserialize($amountData);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param mixed $groupId
     * @param mixed $store
     * @return bool|float|int
     */
    public function getAmoutDataForCustomerGroup($groupId = null, $store = null)
    {
        $amountData = $this->getAmountData($store);

        if (empty($groupId)) {
            $groupId = $this->customerSession->getCustomerGroupId();
        }

        if ($amountData && is_array($amountData)) {
            $minAmount = 0;
            foreach ($amountData as $value) {
                if ($value['customer_group'] == $groupId) {
                    $minAmount = isset($value['minimum_amount']) ? (float) $value['minimum_amount'] : 0;
                }
            }

            return $minAmount;
        }

        return false;
    }

    /**
     * GetMinAmount
     *
     * @return bool|float|int
     */
    public function getMinAmount()
    {
        if (empty($this->getCustomer())) {
            $minAmount = $this->getAmoutDataForCustomerGroup();
        } else {
            if ($this->getCustomer()->getCustomAttribute('minimum_order_amount') == null) {
                $minAmount = $this->getAmoutDataForCustomerGroup();
            } else {
                $minAmount = floatval($this->getCustomer()->getCustomAttribute('minimum_order_amount')->getValue());
            }
        }
        return $minAmount;
    }

    /**
     * @param mixed $groupId
     * @param mixed $store
     * @return mixed|string
     */
    public function getMessage($groupId = null, $store = null)
    {
        $amountData = $this->getAmountData($store);

        if (empty($groupId)) {
            $groupId = $this->customerSession->getCustomerGroupId();
        }
        $message = '';
        if ($amountData) {
            foreach ($amountData as $value) {
                if ($value['customer_group'] == $groupId) {
                    $message = isset($value['message']) ? $value['message'] : '';
                }
            }
        }
        return $message;
    }

    /**
     * Check module is enabled
     *
     * @param int|null $websiteId
     * @return mixed
     */
    public function isModuleEnabled($websiteId = null)
    {
        $isModuleEnabled = $this->scopeConfig->getValue(
            self::BSS_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
        return $isModuleEnabled;
    }
}
