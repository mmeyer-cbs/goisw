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
namespace Bss\QuoteExtension\Helper\Admin\Edit;

/**
 * Class Totals
 *
 * @package Bss\QuoteExtension\Helper\Admin\Edit
 */
class Totals
{
    /**
     * @var \Bss\QuoteExtension\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Totals constructor.
     * @param \Bss\QuoteExtension\Helper\Data $helperData
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        \Bss\QuoteExtension\Helper\Data $helperData,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->helperData = $helperData;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Retrieve formated price
     *
     * @param float $value
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function formatPrice($value)
    {
        return $this->helperData->formatPrice($value);
    }

    /**
     * Get Quote By Id
     *
     * @param int $quoteId
     * @return \Magento\Quote\Api\Data\CartInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuoteById($quoteId)
    {
        return $this->quoteRepository->get($quoteId);
    }
}
