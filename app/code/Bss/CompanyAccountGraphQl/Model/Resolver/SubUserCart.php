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
 * @package    Bss_CompanyAccountGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyAccountGraphQl\Model\Resolver;

use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccountGraphQl\Model\Authorization\TokenSubUserContext;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask as QuoteIdMaskResourceModel;
use Psr\Log\LoggerInterface;

/**
 * Class SubUserCart
 * Returns information about the sub-user shopping cart
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class SubUserCart implements ResolverInterface
{
    /**
     * @var SubUserRepositoryInterface
     */
    protected $subUserRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var QuoteIdMaskResourceModel
     */
    protected $quoteIdMaskResourceModel;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    protected $quoteIdToMaskedQuoteId;

    /**
     * SubUserCart constructor.
     *
     * @param SubUserRepositoryInterface $subUserRepository
     * @param LoggerInterface $logger
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param CartManagementInterface $cartManagement
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param QuoteIdMaskResourceModel $quoteIdMaskResourceModel
     * @param QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
     */
    public function __construct(
        SubUserRepositoryInterface $subUserRepository,
        LoggerInterface $logger,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        CartManagementInterface $cartManagement,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        QuoteIdMaskResourceModel $quoteIdMaskResourceModel,
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId
    ) {
        $this->subUserRepository = $subUserRepository;
        $this->logger = $logger;
        $this->quoteRepository = $quoteRepository;
        $this->cartManagement = $cartManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteIdMaskResourceModel = $quoteIdMaskResourceModel;
        $this->quoteIdToMaskedQuoteId = $quoteIdToMaskedQuoteId;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!$context->getExtensionAttributes()->getIsSubUser()) {
            throw new GraphQlAuthenticationException(
                __("The request is allowed for logged in sub-user.")
            );
        }

        $subId = $context->getExtensionAttributes()->getSubUserId();
        try {
            $subUser = $this->subUserRepository->getById($subId);
            try {
                $quote = $this->quoteRepository->get($subUser->getQuoteId());
            } catch (\Exception $e) {
                // Create new sub-quote
                $newId = $this->cartManagement->createEmptyCartForCustomer($subUser->getCustomerId());
                $quote = $this->quoteRepository->get($newId);
                $quote->setData("bss_is_sub_quote", $subUser->getCustomerId());
                $this->quoteRepository->save($quote);
                $subUser->setQuoteId((int) $quote->getId());
                $this->subUserRepository->save($subUser);
            }

            $this->ensureQuoteMaskIdExist((int) $quote->getId());
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return [
            'model' => $quote ?? null
        ];
    }

    /**
     * Create masked id for customer's active quote if it's not exists
     *
     * @param int $quoteId
     * @param string|null $predefinedMaskedQuoteId
     * @return void
     * @throws AlreadyExistsException
     */
    protected function ensureQuoteMaskIdExist(int $quoteId, string $predefinedMaskedQuoteId = null): void
    {
        try {
            $maskedId = $this->quoteIdToMaskedQuoteId->execute($quoteId);
        } catch (NoSuchEntityException $e) {
            $maskedId = '';
        }
        if ($maskedId === '') {
            $quoteIdMask = $this->quoteIdMaskFactory->create();
            $quoteIdMask->setQuoteId($quoteId);
            if (null !== $predefinedMaskedQuoteId) {
                $quoteIdMask->setMaskedId($predefinedMaskedQuoteId);
            }
            $this->quoteIdMaskResourceModel->save($quoteIdMask);
        }
    }
}
