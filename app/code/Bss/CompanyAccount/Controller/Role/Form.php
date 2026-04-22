<?php
declare(strict_types=1);

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
namespace Bss\CompanyAccount\Controller\Role;

use Bss\CompanyAccount\Helper\Data;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;

/**
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Form extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\Url
     */
    private $url;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var ForwardFactory
     */
    private $resultForwardFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Form constructor.
     *
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param Url $url
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     * @param Data $helper
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Url $url,
        Context $context,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        Data $helper
    ) {
        $this->customerSession = $customerSession;
        $this->url = $url;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException|\Magento\Framework\Exception\SessionException
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->url->getLoginUrl();

        if (!$this->customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        if (!$this->helper->isCompanyAccount() || $this->customerSession->getSubUser()) {
            $resultForward = $this->resultForwardFactory->create();
            return $resultForward->forward('index');
        }
        /** @var \Magento\Framework\View\Element\Html\Links $navigationBlock */
        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('companyaccount/role/');
        }
        return $resultPage;
    }
}
