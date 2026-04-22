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

namespace Bss\CompanyAccount\Controller\Checkout;

use Bss\CompanyAccount\Api\Data\SubUserInterface;
use Bss\CompanyAccount\Api\Data\SubUserQuoteInterface;
use Bss\CompanyAccount\Api\SubUserQuoteRepositoryInterface;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Quote\Model\QuoteFactory;
use Psr\Log\LoggerInterface;

/**
 * Class BackToQuote
 *
 * @package Bss\CompanyAccount\Controller\Checkout
 */
class BackToQuote extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var Onepage
     */
    protected $onePage;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var SubUserQuoteRepositoryInterface
     */
    private $subQuoteRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var SubUserRepositoryInterface
     */
    protected $subUserRepository;

    /**
     * Function construct
     *
     * @param Context $context
     * @param Session $customerSession
     * @param QuoteFactory $quoteFactory
     * @param Onepage $onePage
     * @param JsonFactory $resultJsonFactory
     * @param SubUserQuoteRepositoryInterface $subQuoteRepository
     * @param LoggerInterface $logger
     * @param CheckoutSession $checkoutSession
     * @param SubUserRepositoryInterface $subUserRepository
     */
    public function __construct(
        Context                         $context,
        Session                         $customerSession,
        QuoteFactory                    $quoteFactory,
        Onepage                         $onePage,
        JsonFactory                     $resultJsonFactory,
        SubUserQuoteRepositoryInterface $subQuoteRepository,
        LoggerInterface                 $logger,
        CheckoutSession                 $checkoutSession,
        SubUserRepositoryInterface      $subUserRepository
    ) {
        $this->customerSession = $customerSession;
        $this->quoteFactory = $quoteFactory;
        $this->onePage = $onePage;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->subQuoteRepository = $subQuoteRepository;
        $this->logger = $logger;
        $this->checkoutSession = $checkoutSession;
        $this->subUserRepository = $subUserRepository;
        parent::__construct($context);
    }

    /**
     * Function execute, set current quote (approve quote) to back quote
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|Json
     */
    public function execute()
    {
        /** @var SubUserInterface $subUser */
        $subUser = $this->customerSession->getSubUser();
        $reload = $this->getRequest()->getParam('reload', false);
        $customerId = $this->customerSession->getCustomerId();
        if ($subUser) {
            $backQuote = $this->subQuoteRepository->getByUserId(
                $subUser->getId(),
                SubUserQuoteInterface::SUB_USER_ID
            );
            $this->customerSession->getSubUser()->setQuoteId($backQuote->getQuoteId())->save();
        } else {
            $backQuote = $this->subQuoteRepository->getByUserId($customerId, SubUserQuoteInterface::CUSTOMER_ID);
            if (!$backQuote) {
                $backQuote = $this->subQuoteRepository->getByUserId($customerId, 'NULL');
            }
        }
        if ($backQuote && $this->checkoutSession->getQuoteId() !== $backQuote->getQuoteId()) {
            try {
                $this->onePage->getCheckout()->getQuote()->setIsActive(0)->save();
                $currentQuote = $this->quoteFactory->create()->load($backQuote->getQuoteId());
                $currentQuote->setIsActive(1)->save();
                if ($subUser) {
                    $subUser->setQuoteId($backQuote->getQuoteId());
                    $this->subUserRepository->save($subUser);
                }
                $this->onePage->getCheckout()->replaceQuote($currentQuote);
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }

        if ($reload) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

        return $this->resultJsonFactory->create()->setData(
            [
                'output' => "var sections = ['cart'];
                customerData.invalidate(sections);
                customerData.reload(sections, true);"
            ]
        );
    }
}
