<?php
declare(strict_types = 1);

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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Plugin\Customer;

use Bss\CompanyAccount\Helper\Data;
use Bss\CompanyAccount\Model\ResourceModel\Customer;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\StateException;
use Magento\Store\Model\StoreManagerInterface;
use \Magento\Customer\Controller\Account\CreatePost as CoreCreatePost;

/**
 * Class CreatePost
 *
 * @package Bss\CompanyAccount\Plugin\Customer
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreatePost
{
    /**
     * @var Customer
     */
    private $customerResource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    private $redirect;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;

    /**
     * @var Data
     */
    private $helper;

    /**
     * CreatePost constructor.
     *
     * @param Data $helper
     * @param RedirectFactory $redirectFactory
     * @param Customer $customerResource
     */
    public function __construct(
        Data $helper,
        RedirectFactory $redirectFactory,
        Customer $customerResource
    ) {
        $this->helper = $helper;
        $this->customerResource = $customerResource;
        $this->storeManager = $this->helper->getStoreManager();
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $this->helper->getMessageManager();
        $this->redirect = $this->helper->getRedirect();
        $this->session = $this->helper->getCustomerSession();
    }

    /**
     * Validate unique new customer account email with existed sub-user email
     *
     * If input email is exist in sub-user account
     * redirect back to create page with error message
     * else return to default create post.
     *
     * @param CoreCreatePost|\Bss\B2bRegistration\Controller\Account\CreatePost $subject
     * @param callable $proceed
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute($subject, callable $proceed)
    {
        $defaultUrl = $this->helper->getUrl('*/*/create', ['_secure' => true]);
        if ($subject instanceof \Bss\B2bRegistration\Controller\Account\CreatePost) {
            $objManager = $this->helper->getDataHelper()->getObjectManager();
            /** @var \Bss\B2bRegistration\Block\Account\AuthorizationLink $registrationCreateUrlObj */
            $registrationCreateUrlObj = $objManager->get(\Bss\B2bRegistration\Block\Account\AuthorizationLink::class);
            $b2bRegistrationPath = $registrationCreateUrlObj->getB2bUrl();
            if ($b2bRegistrationPath) {
                $defaultUrl = $this->helper->getUrl($b2bRegistrationPath);
            }
        }
        $resultRedirect = $this->redirectFactory->create();
        $resultRedirect->setUrl($this->redirect->error($defaultUrl));
        try {
            $email = $subject->getRequest()->getParam("email");
            $websiteId = $this->storeManager->getWebsite()->getId();
            $subUserResult = $this->customerResource
                ->validateUniqueCustomer($email, $websiteId);
            if ($subUserResult) {
                $this->session->setCustomerFormData($subject->getRequest()->getPostValue());
                throw new StateException(
                    __('A user with the same email address already exists in an associated website.')
                );
            }
        } catch (StateException $e) {
            $this->messageManager->addComplexErrorMessage(
                'bss_customerAlreadyExistsErrorMessage',
                [
                    'url' => $this->helper->getUrl('customer/account/forgotpassword'),
                ]
            );
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect;
        }

        return $proceed();
    }
}
