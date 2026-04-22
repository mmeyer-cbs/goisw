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
 * @package    Bss_Paymentshipping
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2016 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Paymentshipping\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Bss\Paymentshipping\Model\PaymentshippingFactory
     */
    protected $paymentCollection;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var \Bss\Paymentshipping\Model\PaymentMethodFactory
     */
    protected $methodFactory;

    /**
     * @var HelperPaymentShip
     */
    protected $helperPaymentShip;

    /**
     * @var null
     */
    protected static $customerGroupId = null;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Bss\Paymentshipping\Model\PaymentshippingFactory $paymentCollection
     * @param \Bss\Paymentshipping\Model\PaymentMethodFactory $methodFactory
     * @param \Magento\Framework\App\State $appState
     * @param HelperPaymentShip $helperPaymentShip
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Bss\Paymentshipping\Model\PaymentshippingFactory $paymentCollection,
        \Bss\Paymentshipping\Model\PaymentMethodFactory $methodFactory,
        \Magento\Framework\App\State $appState,
        \Bss\Paymentshipping\Helper\HelperPaymentShip $helperPaymentShip,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->paymentCollection = $paymentCollection;
        $this->methodFactory = $methodFactory;
        $this->appState = $appState;
        $this->helperPaymentShip = $helperPaymentShip;
        $this->customerRepository = $customerRepository;
        parent::__construct($context);
    }

    /**
     * Enable or disable Payment
     *
     * @param int|null $store
     * @return mixed
     */
    public function isEnablePayment($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            'bss_payment_shipping/general/enable_payment',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Enable or disable shipping
     *
     * @param int|null $store
     * @return mixed
     */
    public function isEnableShipping($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            'bss_payment_shipping/general/enable_shipping',
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param string $type
     * @param int $websiteId
     * @param string|null $method
     * @return mixed
     */
    public function getMethodsVisibility($type, $websiteId, $method = null)
    {
        $collection = $this->paymentCollection->create()
            ->getCollection()
            ->addFieldToFilter('type', ['eq' => $type]);
        if ($method !== null) {
            $collection->addFieldToFilter('method', ['eq' => $method]);
        }
        $collection->addFieldToFilter('website_id', ['eq' => $websiteId]);
        return $collection->load();
    }

    /**
     * @param int $idStore
     * @return array
     */
    public function getActivePaymentMethods($idStore)
    {
        return $this->getList($idStore);
    }

    /**
     * @param int $idStore
     * @return \Magento\Shipping\Model\Carrier\AbstractCarrierInterface[]
     */
    public function getActiveShippingMethods($idStore)
    {
        return $this->helperPaymentShip->returnShippingConfig()->getActiveCarriers($idStore);
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getCustomerGroup()
    {
        $groups = $this->helperPaymentShip->returnGroupFactory()->create()->getCollection();
        return $groups;
    }

    /**
     * @param string $method
     * @param string $type
     * @param int|null $customerGroupId
     * @return bool
     */
    public function canUseMethod($method, $type, $websiteId = null, $customerGroupId = null)
    {
        if (!$websiteId) {
            $websiteId = $this->helperPaymentShip->returnStoreManager()->getStore()->getWebsiteId();
        }
        if ($type == 'payment') {
            if (!$this->isEnablePayment()) {
                return true;
            }
            return $this->_canUsePaymentMethod($method, $customerGroupId, $websiteId);
        }
        if ($type == 'shipping') {
            if (!$this->isEnableShipping()) {
                return true;
            }
            return $this->_canUseShippingMethod($method, $customerGroupId, $websiteId);
        }
        return true;
    }

    /**
     * @param string $method
     * @param int $customerGroupId
     * @param int $websiteId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function _canUseShippingMethod($method, $customerGroupId, $websiteId)
    {
        $type = 'shipping';
        $flag = false;
        $collection = $this->getMethodsVisibility($type, $websiteId, $method);
        $customerGroupId = $customerGroupId ? $customerGroupId : $this->_getCustomerGroupId();
        foreach ($collection as $methods) {
            if ($methods->getEntityId()) {
                if ($methods->getGroupIds() != '') {
                    $allowedGroups = explode(',', $methods->getGroupIds());
                    if (in_array($customerGroupId, $allowedGroups)) {
                        $flag = true;
                    } else {
                        $flag = false;
                    }
                } else {
                    $flag = false;
                }
            } else {
                $flag = true;
            }
        }

        if ($flag) {
            return true;
        }

        return false;
    }

    /**
     * @param string $method
     * @param int $customerGroupId
     * @param int $websiteId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _canUsePaymentMethod($method, $customerGroupId, $websiteId)
    {
        $type = 'payment';
        $flag = false;
        $collection = $this->getMethodsVisibility($type, $websiteId, $method);
        $customerGroupId = $customerGroupId ? $customerGroupId : $this->_getCustomerGroupId();
        foreach ($collection as $methods) {
            if ($methods->getEntityId()) {
                if ($methods->getGroupIds() != '') {
                    $allowedGroups = explode(',', $methods->getGroupIds());
                    if (in_array($customerGroupId, $allowedGroups)) {
                        $flag = true;
                    } else {
                        $flag = false;
                    }
                } else {
                    $flag = false;
                }
            } else {
                $flag = true;
            }
        }

        if ($flag) {
            return true;
        }

        return false;
    }

    /**
     * @return int|mixed|null
     */
    public function getCustomerGroupId()
    {
        return $this->_getCustomerGroupId();
    }

    /**
     * @return int|mixed|null
     */
    protected function _getCustomerGroupId()
    {
        $isAdmin = false;
        try {
            if ($this->appState->getAreaCode() == 'adminhtml') {
                $isAdmin = true;
            }
            $customerQuote = $this->helperPaymentShip->returnBackendQuote()->create();
            $customerIdBackend = $customerQuote->getCustomerId();
            if ($isAdmin && $customerIdBackend) {
                $order = $this->_request->getParam('order');
                if (isset($order['account']['group_id'])) {
                    $roleId = $order['account']['group_id'];
                } else {
                    $roleId = $this->customerRepository->getById($customerIdBackend)->getGroupId();
                }
            } else {
                $roleId = (int)$this->helperPaymentShip->returnHttpContext()->getValue('logged_in_customer_id');
                $customerSession = $this->helperPaymentShip->returnCustomerSession()->create();
                if ($roleId == 0) {
                    if (self::$customerGroupId !== null) {
                        return self::$customerGroupId;
                    }
                    if (!$customerSession->getId()) {
                        return 0;
                    }
                }
                if ($customerSession->getId()) {
                    $roleId = $this->helperPaymentShip->returnModelCustomer()
                        ->load($customerSession->getId())
                        ->getData('group_id');
                }
            }
        } catch (\Exception $exception) {
            throw new \LogicException(__($exception->getMessage()));
        }

        return $roleId;
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function getList($storeId)
    {
        $methodCodes = $this->scopeConfig->getValue('payment', ScopeInterface::SCOPE_STORE, $storeId);
        $methodList = [];
        foreach ($methodCodes as $code => $value) {
            if (!isset($value['active']) || !$value['active']) {
                continue;
            }
            $data = [];
            $data['code'] = $code;
            $data['title'] = isset($value['title']) ? $value['title'] : '';
            if ($code == 'wps_express' && isset($methodCodes['paypal_express']['title'])) {
                $data['title'] = $methodCodes['paypal_express']['title'];
            }
            $methodList[] =$data;
        }

        return $methodList;
    }
}
