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
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\QuoteExtension\Plugin\Model;

/**
 * Class QuoteRepository
 *
 * @package Bss\QuoteExtension\Plugin\Model
 */
class QuoteRepository
{
    /**
     * @var \Bss\QuoteExtension\Helper\Data
     */
    protected $quoteExtensionHelper;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * Session constructor.
     * @param \Bss\QuoteExtension\Helper\Data $quoteExtensionHelper
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     */
    public function __construct(
        \Bss\QuoteExtension\Helper\Data $quoteExtensionHelper,
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {
        $this->quoteExtensionHelper = $quoteExtensionHelper;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * Get active quote by id
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $subject
     * @param callable $proceed
     * @param int $cartId
     * @param array $sharedStoreIds
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundGetActive(
        $subject,
        callable $proceed,
        $cartId,
        array $sharedStoreIds = []
    ) {
        if ($cartId === null) {
            return $this->quoteFactory->create();
        }
        $quote = $subject->get($cartId, $sharedStoreIds);
        if ($quote->getQuoteExtension() && !$quote->getIsActive()) {
            return $quote;
        }
        return $proceed($cartId, $sharedStoreIds);
    }

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $subject
     * @param \Bss\QuoteExtension\Model\Quote $result
     * @param int $cartId
     * @param array $sharedStoreIds
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        $subject,
        $result,
        $cartId,
        array $sharedStoreIds = []
    ) {
        if (!$this->quoteExtensionHelper->validateQuantity() && $result->getQuoteExtension()) {
            $result->setIsSuperMode(true);
        }

        return $result;
    }
}
