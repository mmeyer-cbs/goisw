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
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyAccount\Model;

use Psr\Log\LoggerInterface;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Customer\Model\Session;
use Magento\Quote\Api\CartRepositoryInterface;

class Checkout
{
    /**
     * @var Onepage
     */
    protected $onePage;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Onepage $onePage
     * @param Session $customerSession
     * @param CartRepositoryInterface $quoteRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Onepage                     $onePage,
        Session                     $customerSession,
        CartRepositoryInterface     $quoteRepository,
        LoggerInterface             $logger
    ) {
        $this->customerSession = $customerSession;
        $this->onePage = $onePage;
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
    }

    /**
     * Replace Quote
     *
     * @param $quoteId
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function replaceQuote($quoteId)
    {
        if ($subUser = $this->customerSession->getSubUser()) {
            $subUser->setQuoteId($quoteId)->save();
        } else {
            try {
                $this->onePage->getCheckout()->getQuote()->setIsActive(0)->save();
            } catch (\Exception $e) {
                $this->logger->error($e);
            }
        }
        $approveQuote = $this->quoteRepository->get($quoteId);
        $approveQuote->setIsActive(1);
        $this->quoteRepository->save($approveQuote);
        $this->onePage->getCheckout()->replaceQuote($approveQuote);
    }
}
