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

namespace Bss\Paymentshipping\Block\Adminhtml\Paymentshipping;

use Bss\Paymentshipping\Helper\Data;
use Magento\Backend\Block\Template;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Index extends Template
{
    /**
     * @var RequestInterface|string
     */
    protected $type = '';

    /**
     * @var array
     */
    protected $visibility = [];

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var Registry|null
     */
    protected $coreRegistry = null;

    /**
     * @var StoreManagerInterface|null
     */
    protected $storeManager = null;

    /**
     * @var GroupFactory
     */
    protected $groupFactory;

    /**
     * Index constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param Context $appContext
     * @param Registry $registry
     * @param GroupFactory $groupFactory
     * @param Data $dataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        Context                               $appContext,
        Registry                              $registry,
        GroupFactory                          $groupFactory,
        Data                                  $dataHelper,
        array                                 $data = []
    ) {
        $this->type = $appContext->getRequest();
        $this->request = $appContext->getRequest();
        $this->scopeConfig = $context->getScopeConfig();
        $this->coreRegistry = $registry;
        $this->storeManager = $context->getStoreManager();
        $this->groupFactory = $groupFactory;
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    /**
     *
     */
    protected function _prepareVisibility()
    {
        $collection = $this->dataHelper->getMethodsVisibility($this->type->getActionName(), $this->getCurrentWebsite());
        foreach ($collection as $method) {
            if ($method->getGroupIds() !== null && $method->getGroupIds() !== false) {
                $this->visibility[$method->getMethod()] = explode(',', $method->getGroupIds());
            }
        }
    }

    /**
     * @return string
     */
    public function getMethodsType()
    {
        return ucwords($this->type->getActionName());
    }

    /**
     * @return array
     */
    public function getMethods()
    {
        $methods = [];
        if ('payment' == $this->type->getActionName()) {
            $methods = $this->_getPaymentMethods();
        } elseif ('shipping' == $this->type->getActionName()) {
            $methods = $this->_getShippingMethods();
        }
        return $methods;
    }

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        $params = ['_current' => 'true'];
        return $this->getUrl('*/*/save', $params);
    }

    /**
     * @param int|null $website
     * @return string
     */
    public function getWebsiteUrl($website = null)
    {
        if (!$website) {
            $websiteId = $this->storeManager->getDefaultStoreView()->getWebsiteId();
        } else {
            $websiteId = $website->getId();
        }
        return $this->getUrl('*/*/*', ['website_id' => $websiteId, '_current' => true]);
    }

    /**
     * @return WebsiteInterface[]
     */
    public function getWebsites()
    {
        return $this->storeManager->getWebsites();
    }

    /**
     * @return mixed
     */
    public function getCurrentWebsite()
    {
        $websiteId = $this->request->getParam('website_id');
        if (!$websiteId) {
            $websiteId = $this->storeManager->getDefaultStoreView()->getWebsiteId();
        }
        return $websiteId;
    }

    /**
     * @return array
     */
    public function getCustomerGroups()
    {
        $groups = $this->dataHelper->getCustomerGroup();
        $options = [];
        foreach ($groups as $eachGroup) {
            $option['value'] = $eachGroup->getCustomerGroupId();
            $option['label'] = $eachGroup->getCustomerGroupCode();
            $options[] = $option;
        }

        return $options;
    }

    /**
     * @param array $group
     * @param string $methodCode
     * @return bool
     */
    public function isGroupSelected($group, $methodCode)
    {
        $this->_prepareVisibility();
        if (isset($this->visibility[$methodCode]) && in_array($group['value'], $this->visibility[$methodCode])) {
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    protected function _getPaymentMethods()
    {
        $firstStoreViewIdCurrent = $this->getFirstStoreViewCurrent(null);
        $payments = $this->dataHelper->getActivePaymentMethods($firstStoreViewIdCurrent);
        $methods = [];
        foreach ($payments as $value) {
            $title = isset($value['title']) ? $value['title'] . ' (' . $value['code'] . ')' : '(' . $value['code'] . ')';
            if ($value['code'] == 'wps_express') {
                $methods['paypal_express'] = [
                    'title' => $title,
                    'value' => 'paypal_express'
                ];
            } else {
                $methods[$value['code']] = [
                    'title' => $title,
                    'value' => $value['code']
                ];
            }
        }
        return $methods;
    }

    /**
     * @param string $param
     * @return mixed
     */
    protected function getFirstStoreViewCurrent($param = 'code')
    {
        $groupId = $this->getCurrentWebsite();
        $sotresView = $this->groupFactory->create()->getCollection()->addFieldToFilter('website_id', $groupId);
        foreach ($sotresView as $storeView) {
            foreach ($storeView->getStores() as $myStore) {
                if ($param == 'code') {
                    return $myStore->getCode();
                } else {
                    return $myStore->getId();
                }
            }
        }
        return '';
    }

    /**
     * @return array
     */
    protected function _getShippingMethods()
    {
        $firstStoreViewIdCurrent = $this->getFirstStoreViewCurrent();
        $scopCode = $this->getFirstStoreViewCurrent('code');
        $shipping = $this->dataHelper->getActiveShippingMethods($firstStoreViewIdCurrent);
        $methods = [];
        $shippingCodes = array_keys($shipping);
        foreach ($shippingCodes as $shippingCode) {
            $shippingTitle = $this->scopeConfig->getValue(
                'carriers/' . $shippingCode . '/title',
                ScopeInterface::SCOPE_STORE,
                $scopCode
            );
            $methods[$shippingCode] = [
                'title' => $shippingTitle,
                'value' => $shippingCode,
            ];
        }
        return $methods;
    }
}
