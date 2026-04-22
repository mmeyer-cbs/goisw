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
 * @package    Bss_B2bRegistration
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\B2bRegistration\Plugin;

use Bss\B2bRegistration\Helper\Data;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http as responseHttp;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Bss\B2bRegistration\Model\Config\Source\CustomerAttribute;

class LoginPost
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerInterface;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var responseHttp
     */
    protected $response;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * LoginPost constructor.
     * @param Data $helper
     * @param Context $context
     * @param responseHttp $response
     * @param CustomerRepositoryInterface $customerInterface
     */
    public function __construct(
        Data $helper,
        Context $context,
        responseHttp $response,
        CustomerRepositoryInterface $customerInterface
    ) {
        $this->helper = $helper;
        $this->request = $context->getRequest();
        $this->messageManager = $context->getMessageManager();
        $this->url = $context->getUrl();
        $this->response = $response;
        $this->customerInterface = $customerInterface;
    }

    /**
     * Check Customer Login
     * @param \Magento\Customer\Controller\Account\LoginPost $subject
     * @param \Closure $proceed
     * @return $this|mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(\Magento\Customer\Controller\Account\LoginPost $subject, \Closure $proceed)
    {
        if ($this->helper->isEnable()) {
            $login =  $this->request->getPost('login');
            $email = $login['username'];
            try {
                $customer = $this->customerInterface->get($email);
                $customerAttr = $customer->getCustomAttribute('b2b_activasion_status');
                if ($customerAttr) {
                    $customerValue = $customerAttr->getValue();
                    if ($customerValue == CustomerAttribute::B2B_PENDING) {
                        $message = $this->helper->getPendingMess();
                        $loginUrl = $this->url->getUrl('customer/account/login');
                        $this->messageManager->addErrorMessage($message);
                        return $this->response->setRedirect($loginUrl);
                    } elseif ($customerValue == CustomerAttribute::B2B_REJECT) {
                        $message = $this->helper->getDisapproveMess();
                        $loginUrl = $this->url->getUrl('customer/account/login');
                        $this->messageManager->addErrorMessage($message);
                        return $this->response->setRedirect($loginUrl);
                    }
                }
                return $proceed();
            } catch (\Exception $e) {
                return $proceed();
            }
        }
        return $proceed();
    }
}
