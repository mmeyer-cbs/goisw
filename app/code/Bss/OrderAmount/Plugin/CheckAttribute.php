<?php

namespace Bss\OrderAmount\Plugin;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\RequestInterface;

class CheckAttribute
{
    /**
     * @param ResultFactory $Redirect
     * @param ManagerInterface $messageManager
     * @param RequestInterface $request
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Bss\OrderAmount\Helper\Data $helper
     */
    public function __construct(
        ResultFactory                                      $Redirect,
        ManagerInterface                                   $messageManager,
        RequestInterface                                   $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Bss\OrderAmount\Helper\Data                       $helper
    ) {
        $this->resultFactory = $Redirect;
        $this->_messageManager = $messageManager;
        $this->getRequest = $request;
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
    }

    /**
     * Validate MinAmount
     *
     * @param \Magento\Customer\Controller\Adminhtml\Index\Save $subject
     * @param string $proceed
     * @param string $data
     * @param boolean $requestInfo
     * @return \Magento\Framework\Controller\Result\Redirect|mixed
     */
    public function aroundexecute(
        \Magento\Customer\Controller\Adminhtml\Index\Save $subject,
                                                          $proceed,
                                                          $data = "null",
                                                          $requestInfo = false
    ) {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $dataCustomer = $subject->getRequest()->getPostValue();
        if (!empty($dataCustomer['customer'])) {
            $websiteId = $dataCustomer['customer']['website_id'];
            if ($this->helper->isModuleEnabled($websiteId)) {
                $groupMinAmount = $this->helper->getAmoutDataForCustomerGroup($dataCustomer['customer']['group_id']);
                $cusomterMinAmount = $dataCustomer['customer']['minimum_order_amount'];
                //code to validate start and end date
                if ($cusomterMinAmount=='' || floatval($cusomterMinAmount)==floatval($groupMinAmount)) {
                    $this->_messageManager->addSuccessMessage(__('Using Group Minimum Order Amount !! '));
                } elseif (is_numeric($cusomterMinAmount) == false) {
                    $this->_messageManager->addError(__('Minimum Order Amount must be number!!'));
                    return $resultRedirect->setRefererOrBaseUrl();
                } elseif (floatval($cusomterMinAmount) < 0) {
                    $this->_messageManager->addError(__('Minimum Order Amount not allowed negative number !! '));
                    return $resultRedirect->setRefererOrBaseUrl();
                } elseif (floatval($cusomterMinAmount)!=floatval($groupMinAmount)) {
                    $this->_messageManager->addSuccessMessage(__('Using Custom Minimum Order Amount !! '));
                }
            }
        }

        return $proceed();
    }
}
