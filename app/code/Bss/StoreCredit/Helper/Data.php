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
 * @package    Bss_StoreCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\StoreCredit\Helper;

use Bss\StoreCredit\Model\ResourceModel\History\CollectionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Sales\Api\Data\OrderExtension;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 * @package Bss\StoreCredit\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var \Bss\StoreCredit\Model\ResourceModel\History\CollectionFactory
     */
    private $historyCollection;

    /**
     * @var \Magento\Sales\Api\Data\OrderExtension
     */
    private $orderExtension;

    /**
     * @param OrderExtension $orderExtension
     * @param CollectionFactory $historyCollection
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     */
    public function __construct(
        \Magento\Sales\Api\Data\OrderExtension                         $orderExtension,
        \Bss\StoreCredit\Model\ResourceModel\History\CollectionFactory $historyCollection,
        StoreManagerInterface                                          $storeManager,
        Context                                                        $context
    ) {
        parent::__construct($context);
        $this->orderExtension = $orderExtension;
        $this->historyCollection = $historyCollection;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $field
     * @param int|null $storeId
     * @return mixed
     */
    public function getEmailConfig($field, $storeId = null)
    {
        $scope = ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('storecredit/email/' . $field, $scope, $storeId);
    }

    /**
     * @param string $field
     * @param int|null $storeId
     * @return mixed
     */
    public function getGeneralConfig($field, $storeId = null)
    {
        $scope = ScopeInterface::SCOPE_STORE;
        if (!$this->scopeConfig->getValue('storecredit/general/active', $scope)) {
            return false;
        }

        return $this->scopeConfig->getValue('storecredit/general/' . $field, $scope, $storeId);
    }

    /**
     * Get action store credit
     *
     * @param int $value
     * @return string
     */
    public function getTypeAction($value)
    {
        $result = '';
        switch ($value) {
            case 1:
                $result = __('Refund');
                break;
            case 2:
                $result = __('Used in order');
                break;
            case 3:
                $result = __('Update');
                break;
            case 4:
                $result = __('Revert');
                break;
        }
        return $result;
    }

    /* Get website id by store
     *
     * @return int|null
     */
    public function getWebsiteIdbyStore()
    {
        try {
            return $this->storeManager->getStore()->getWebsiteId();
        } catch (\Exception $exception) {
            $this->_logger->critical($exception->getMessage());
            return null;
        }
    }

    /**
     * Get success apply credit message
     *
     * @return string
     */
    public function getSuccessApplyCreditMsg()
    {
        $message = 'Success';
        if (!$this->getGeneralConfig('used_tax') &&
            !$this->getGeneralConfig('used_shipping')
        ) {
            $message = 'Success! Note: Store credit is not applied to tax & shipping fee.';
        } elseif (!$this->getGeneralConfig('used_tax')) {
            $message = 'Success! Note: Store credit is not applied to tax.';
        } elseif (!$this->getGeneralConfig('used_shipping')) {
            $message = 'Success! Note: Store credit is not applied to shipping fee.';
        }
        return $message;
    }

    /**
     * @param \Magento\Sales\Model\Order|\Magento\Sales\Api\Data\OrderInterface $order
     * @return void
     */
    public function addStoreCreditToExtensionAttrOrder($order)
    {
        if (isset($order['bss_storecredit_amount']) && $order['bss_storecredit_amount']) {
            $orderId = $order->getEntityId();
            $collectionHistory = $this->historyCollection->create();
            $storeCreditData = $collectionHistory->getStoreCreditHistoryRecord($orderId);
            if ($storeCreditData) {
                $extensionAttributes = $order->getExtensionAttributes();
                if ($extensionAttributes === null) {
                    $extensionAttributes = $this->orderExtension;
                }

                $extensionAttributes->setBssStoreCredit($storeCreditData);
                $order->setExtensionAttributes($extensionAttributes);
            }
        }
    }
}
