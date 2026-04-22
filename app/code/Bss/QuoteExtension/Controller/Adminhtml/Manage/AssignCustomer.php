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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\QuoteExtension\Controller\Adminhtml\Manage;

use Bss\QuoteExtension\Model\CustomerAssignment;
use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Agree
 *
 * @package Bss\QuoteExtension\Controller\Adminhtml\Manage
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class AssignCustomer extends \Magento\Backend\App\Action
{
    /**
     * @var \Bss\QuoteExtension\Model\ManageQuote
     */
    protected $manageQuote;

    /**
     * @var CustomerAssignment
     */
    protected $assignmentService;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * AssignCustomer constructor.
     * @param CustomerAssignment $assignmentService
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Bss\QuoteExtension\Model\ManageQuote $manageQuote
     */
    public function __construct(
        CustomerAssignment $assignmentService,
        StoreManagerInterface $storeManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Backend\App\Action\Context $context,
        \Bss\QuoteExtension\Model\ManageQuote $manageQuote
    ) {
        $this->assignmentService = $assignmentService;
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->manageQuote = $manageQuote;
        parent::__construct($context);
    }

    /**
     * Assign customer to quote
     *
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('entity_id');

        if ($id) {
            try {
                $this->manageQuote->load($id);
                if ($this->manageQuote->getId()) {
                    if ($customer = $this->getCustomer($this->manageQuote->getCustomerEmail())) {
                        $this->assignmentService->execute($this->manageQuote, $customer);
                        $this->assignmentService->assignCustomerToQuoteBackend($this->manageQuote, $customer);
                        $this->messageManager->addSuccessMessage(__('You assigned customer to quote'));
                        return $resultRedirect->setPath('*/*/index');
                    }
                }

            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
            return $resultRedirect->setPath('*/*/edit', ['entity_id' => $this->manageQuote->getId()]);
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a quote.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Get customer by email and website id
     *
     * @param string $customerEmail
     * @return false|\Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer($customerEmail)
    {
        try {
            $websiteId = $this->storeManager->getStore($this->manageQuote->getStoreId())->getWebsiteId();
        } catch (\Exception $exception) {
            $websiteId = null;
        }
        try {
            return $this->customerRepository->get($customerEmail, $websiteId);
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage("You must create account to assign quote");
            return false;
        }
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bss_QuoteExtension::assign_customer_to_quote');
    }
}
