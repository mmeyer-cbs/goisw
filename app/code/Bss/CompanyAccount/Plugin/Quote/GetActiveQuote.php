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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyAccount\Plugin\Quote;

use Bss\CompanyAccount\Model\Checkout;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Api\SubUserQuoteRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;

class GetActiveQuote
{
    /**
     * @var Checkout
     */
    private $checkout;

    /**
     * @var PermissionsChecker
     */
    private $permissionsChecker;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var CustomerSessionFactory
     */
    private $customerSessionFactory;

    /**
     * @var SubUserQuoteRepositoryInterface
     */
    private $subUserQuoteRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param Checkout $checkout
     */
    public function __construct(
        PermissionsChecker              $permissionsChecker,
        CartRepositoryInterface         $quoteRepository,
        CustomerSessionFactory          $customerSessionFactory,
        Checkout                        $checkout,
        LoggerInterface                 $logger,
        SubUserQuoteRepositoryInterface $subUserQuoteRepository
    ) {
        $this->permissionsChecker = $permissionsChecker;
        $this->quoteRepository = $quoteRepository;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->checkout = $checkout;
        $this->subUserQuoteRepository = $subUserQuoteRepository;
        $this->logger = $logger;
    }

    /**
     * Set Quote for SubUser and Replace Quote
     *
     * @param object $subject
     * @param $result
     * @return mixed|void
     */
    public function afterGetActiveQuote($subject, $result)
    {
        $params = $subject->getRequest()->getParams();
        if (isset($params['companyaccount']) && $params['companyaccount'] === '1') {
            $quoteId = $params['order_id'];
            try {
                $approveQuote = $this->quoteRepository->get($quoteId);
                $customerSession = $this->customerSessionFactory->create();
                $customerID = $customerSession->getCustomerId();
                $subUser = $customerSession->getSubUser();
                if ($approveQuote->getData('bss_is_sub_quote') == $customerID) {
                    if (($subUser && !$this->permissionsChecker->isDenied(Permissions::PLACE_ORDER))
                        || $this->permissionsChecker->isAdmin()) {
                        $this->checkout->replaceQuote($quoteId);
                    } elseif (!$this->permissionsChecker->isDenied(Permissions::PLACE_ORDER_WAITING)
                        && $this->canCheckOut($quoteId)) {
                        $this->checkout->replaceQuote($quoteId);
                    }
                }
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
        return $result;
    }

    /**
     * Quote can Checkout
     *
     * @param int $quoteId
     * @return bool
     */
    public function canCheckOut($quoteId)
    {
        return $this->subUserQuoteRepository->getByQuoteId($quoteId)->getQuoteStatus() == 'approved';
    }
}
