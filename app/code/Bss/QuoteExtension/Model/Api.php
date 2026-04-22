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
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Api
 */
class Api
{
    /**
     * @var \Magento\Quote\Model\QuoteRepository\LoadHandler
     */
    protected $quoteLoadHandler;

    /**
     * @var ManageQuoteRepository
     */
    protected $manageQuoteRepository;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Request object
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var int
     */
    public $customerGroupId;

    /**
     * Data constructor.
     *
     * @param CustomerRepositoryInterface $customerRepositoryInterface
     * @par Context $context
     */
    public function __construct(
        \Magento\Quote\Model\QuoteRepository\LoadHandler $quoteLoadHandler,
        \Bss\QuoteExtension\Model\ManageQuoteRepository $manageQuoteRepository,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        LoggerInterface $logger,
        RequestInterface $request,
        CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->quoteLoadHandler = $quoteLoadHandler;
        $this->manageQuoteRepository = $manageQuoteRepository;
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
        $this->request = $request;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
    }

    /**
     * Get customer group id by id customer
     *
     * @param int|null $customerId
     * @return int|null
     */
    public function getCustomerGroupId($customerId =null)
    {
        if (!$this->customerGroupId) {
            try {
                $this->customerGroupId = 0;
                if ($customerId) {
                    $customer = $this->customerRepositoryInterface->getById($customerId);
                    $this->customerGroupId = (int) $customer->getGroupId();
                }
                return $this->customerGroupId;
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage());
                return null;
            }
        }
        return $this->customerGroupId;
    }

    /**
     * Get customer id
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->request->getParam("customerId");
    }

    /**
     * Get quote id by quote extension entity id
     *
     * @param int $qEEntityId
     * @return int
     */
    public function getQuoteIdByQEEntityTy($qEEntityId)
    {
        $manaQuote = $this->manageQuoteRepository->getById($qEEntityId);
        if ($manaQuote->getStatus() == \Bss\QuoteExtension\Model\Config\Source\Status::STATE_UPDATED &&
            $manaQuote->getMoveCheckout() !== 0

        ) {
            return $manaQuote->getQuoteId();
        }
        return 0;
    }

}
