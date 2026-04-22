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
namespace Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class SelectRate
 *
 * @package Bss\QuoteExtension\Block\Adminhtml\Quote\View
 */
class SelectRate extends \Magento\Sales\Block\Adminhtml\Order\Create\Data
{
    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Totals constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Framework\Registry $registry,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        array $data
    ) {
        $this->registry = $registry;
        $this->quoteRepository = $quoteRepository;
        parent::__construct(
            $context,
            $sessionQuote,
            $orderCreate,
            $priceCurrency,
            $currencyFactory,
            $localeCurrency,
            $data
        );
    }

    /**
     * Retrieve store model object
     *
     * @return \Magento\Store\Model\Store
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore()
    {
        return $this->getQuote()->getStore();
    }

    /**
     * Retrieve quote model object
     *
     * @return \Magento\Quote\Model\Quote|false
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuote()
    {
        if (!$quote = $this->registry->registry('mage_quote')) {
            $quoteId = $this->getRequest()->getParam('quote_id');
            if (!$quoteId) {
                return false;
            }
            $this->registry->register(
                'mage_quote',
                $this->quoteRepository->get($quoteId)
            );
        }
        return $quote;
    }
}
