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
 * @package    Bss_SalesRep
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SalesRep\Plugin\Controller\Adminhtml;

use Bss\SalesRep\Helper\Data;
use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Authorization;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Class Edit
 *
 * @package Bss\SalesRep\Plugin\Controller\Adminhtml
 */
class Edit
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var RedirectFactory
     */
    protected $redirect;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var SessionManagerInterface
     */
    protected $coreSession;

    /**
     * @var Authorization
     */
    protected $authorization;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $adminSession;

    /**
     * @var Collection
     */
    protected $customerCollection;

    /**
     * Edit constructor.
     * @param SessionManagerInterface $coreSession
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $redirect
     * @param Data $helper
     * @param RequestInterface $request
     * @param Authorization $authorization
     * @param \Magento\Backend\Model\Auth\Session $adminSession
     * @param Collection $customerCollection
     */
    public function __construct(
        SessionManagerInterface $coreSession,
        ManagerInterface $messageManager,
        RedirectFactory $redirect,
        Data $helper,
        RequestInterface $request,
        Authorization $authorization,
        \Magento\Backend\Model\Auth\Session $adminSession,
        Collection $customerCollection
    ) {
        $this->coreSession = $coreSession;
        $this->helper = $helper;
        $this->request = $request;
        $this->redirect = $redirect;
        $this->messageManager = $messageManager;
        $this->authorization = $authorization;
        $this->adminSession = $adminSession;
        $this->customerCollection = $customerCollection;
    }

    /**
     * Check customer assigned User is Sales Rep.
     *
     * @param \Magento\Customer\Controller\Adminhtml\Index\Edit $edit
     * @param Redirect $result
     * @return Redirect
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterExecute(
        \Magento\Customer\Controller\Adminhtml\Index\Edit $edit,
        $result
    ) {
        $customerAllowed = $this->authorization->isAllowed('Magento_Customer::customer');
        $customerIds = $this->coreSession->getCustomerIds() ?? [];
        $id = $this->request->getParam('id', '');
        $userId = $this->adminSession->getUser()->getId();
        $resultRedirect = $this->redirect->create();
        if (!empty($id) && $this->helper->isEnable()) {
            if ($this->helper->checkUserIsSalesRep() && !$customerAllowed) {
                if (!in_array($id, $customerIds)) {
                    $customerCollection = $this->customerCollection
                        ->addFieldToSelect('*')
                        ->addAttributeToFilter('bss_sales_representative', $userId);
                    $customerIds = [];
                    foreach ($customerCollection as $customer) {
                        $customerIds[] = $customer['entity_id'];
                    }
                    $this->coreSession->setCustomerIds($customerIds);
                    if (in_array($id, $customerIds)) {
                        return $result;
                    }
                    $this->messageManager->addErrorMessage(__('Something went wrong while editing the customer.'));
                    $resultRedirect->setPath('salesrep/index/customer');
                    return $resultRedirect;
                }
            }
        }
        return $result;
    }
}
