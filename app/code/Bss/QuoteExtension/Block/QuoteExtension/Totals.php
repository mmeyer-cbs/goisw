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
namespace Bss\QuoteExtension\Block\QuoteExtension;

use Bss\QuoteExtension\Model\Config\Source\Status;

/**
 * Class Totals
 *
 * @package Bss\QuoteExtension\Block\QuoteExtension
 */
class Totals extends \Magento\Sales\Block\Order\Totals
{
    /**
     * @var $quote
     */
    protected $quote;

    /**
     * @var \Bss\QuoteExtension\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $objectFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Bss\QuoteExtension\Helper\CartHidePrice
     */
    protected $cartHidePrice;

    /**
     * Totals constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Bss\QuoteExtension\Helper\Data $helperData
     * @param \Magento\Framework\DataObjectFactory $objectFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Bss\QuoteExtension\Helper\CartHidePrice $cartHidePrice
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Bss\QuoteExtension\Helper\Data $helperData,
        \Magento\Framework\DataObjectFactory $objectFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Bss\QuoteExtension\Helper\CartHidePrice $cartHidePrice,
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->objectFactory = $objectFactory;
        $this->productRepository = $productRepository;
        $this->cartHidePrice = $cartHidePrice;
        parent::__construct($context, $registry, $data);
    }

    /**
     * Get totals source object
     *
     * @return \Bss\QuoteExtension\Model\Quote
     */
    public function getSource()
    {
        return $this->getQuoteExtension();
    }

    /**
     * Get active quote
     *
     * @return \Bss\QuoteExtension\Model\Quote
     */
    public function getQuoteExtension()
    {
        if (null === $this->quote) {
            $this->quote = $this->_coreRegistry->registry('current_quote');
        }
        return $this->quote;
    }

    /**
     * Initialize order totals array
     *
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _initTotals()
    {
        $source = $this->getSource();

        $this->_totals = [];
        if ($source->getIsVirtual() == 1) {
            $quoteTotals = $source->getBillingAddress()->getTotals();
        } else {
            $quoteTotals = $source->getShippingAddress()->getTotals();
        }
        foreach ($quoteTotals as $total) {
            if (!$this->canShowSubtotal($total)) {
                continue;
            }
            if ($total->getCode() == "grand_total") {
                $this->_totals[$total->getCode()] = $this->objectFactory->create()->setData(
                    [
                        'strong' => true,
                        'code' => $total->getCode(),
                        'field' => $total->getCode() . '_amount',
                        'value' => $total->getValue(),
                        'label' => __($total->getTitle()),
                    ]
                );
            } else {
                $this->_totals[$total->getCode()] = $this->objectFactory->create()->setData(
                    [
                        'code' => $total->getCode(),
                        'field' => $total->getCode() . '_amount',
                        'value' => $total->getValue(),
                        'label' => __($total->getTitle()),
                    ]
                );
            }
        }

        return $this;
    }

    /**
     * Format total value based on order currency
     *
     * @param \Magento\Framework\DataObject $total
     * @return float|string
     */
    public function formatValue($total)
    {
        $source = $this->getSource();
        $storeId = $source->getStoreId();
        if (!$total->getIsFormated()) {
            return $this->helperData->formatPrice($total->getValue(), $storeId, $source->getQuoteCurrencyCode());
        }
        return $total->getValue();
    }

    /**
     * Can show subtotal
     *
     * @param \Magento\Framework\DataObject $total
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function canShowSubtotal($total)
    {
        $manaQuote = $this->_coreRegistry->registry('current_quote_extension');
        if ($manaQuote->getStatus() === Status::STATE_PENDING
            || $manaQuote->getStatus() === Status::STATE_CANCELED
            || $manaQuote->getStatus() === Status::STATE_REJECTED
        ) {
            if ($total->getCode() == "grand_total" || $total->getCode() == "subtotal") {
                foreach ($this->getQuoteExtension()->getAllVisibleItems() as $item) {
                    if ($item->getProductType() == 'configurable') {
                        $parentProductId = $item->getProductId();
                        $childProductSku = $item->getSku();
                        $canShowPrice = $this->cartHidePrice->canShowPrice($parentProductId, $childProductSku);
                    } else {
                        $canShowPrice = $this->cartHidePrice->canShowPrice($item->getProductId(), false);
                    }
                    if (!$canShowPrice) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
}
