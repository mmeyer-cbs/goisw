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
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\QuoteExtension\Plugin\CompanyAccount;

use Magento\Checkout\Model\Session as CheckoutSession;
class SendRequest
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Bss\QuoteExtension\Model\ManageQuoteRepository
     */
    protected $manageQuoteRepository;

    /**
     * Construct
     *
     * @param CheckoutSession $checkoutSession
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Bss\QuoteExtension\Model\ManageQuoteRepository $manageQuoteRepository
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        \Magento\Framework\App\RequestInterface $request,
        \Bss\QuoteExtension\Model\ManageQuoteRepository $manageQuoteRepository
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->request = $request;
        $this->manageQuoteRepository = $manageQuoteRepository;
    }

    /**
     * Set manage quote after send order request
     *
     * @param \Bss\CompanyAccount\Controller\Order\SendRequest $subject
     * @param $someThing
     * @param \Bss\CompanyAccount\Model\SubUser $subUser
     * @return void
     * @throws \Exception
     */
    public function afterGenerateOrderRequest($subject, $someThing, $subUser)
    {
        $quoteRequest = $this->request->getServer('HTTP_REFERER') ?? "";
        $checkSendRqQuote = str_contains($quoteRequest, 'quote_id');
        if ($checkSendRqQuote) {
            $extensionQuoteId = $this->checkoutSession->getData('is_quote_extension');
            $manageQuote = $this->manageQuoteRepository->getByQuoteId($extensionQuoteId);
            if ($manageQuote->getQuoteId()) {
                $manageQuote->setBackendQuoteId($manageQuote->getQuoteId());
                $manageQuote->setTargetQuote($manageQuote->getQuoteId());
                $manageQuote->setData('status', 'order-sent');
                $manageQuote->setData('token', '');
                $manageQuote->save();
            }
        }
    }
}
