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
namespace Bss\QuoteExtension\Block\QuoteExtension\View;

use Bss\QuoteExtension\Helper\Data as HelperData;
use Bss\QuoteExtension\Helper\QuoteExtension\ExpiredQuote;
use Bss\QuoteExtension\Helper\QuoteExtension\Status as HeplerStatus;
use Bss\QuoteExtension\Model\Config\Source\Status;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context as TemplateContext;

/**
 * Class Info
 *
 * @package Bss\QuoteExtension\Block\QuoteExtension\View
 */
class Info extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var ExpiredQuote
     */
    protected $helperExpired;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var HeplerStatus
     */
    protected $helperStatus;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Bss\QuoteExtension\Helper\CartHidePrice
     */
    protected $cartHidePrice;

    /**
     * Info constructor.
     * @param TemplateContext $context
     * @param Registry $registry
     * @param ExpiredQuote $helperExpired
     * @param HelperData $helperData
     * @param HeplerStatus $helperStatus
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Bss\QuoteExtension\Helper\CartHidePrice $cartHidePrice
     * @param array $data
     */
    public function __construct(
        TemplateContext $context,
        Registry $registry,
        ExpiredQuote $helperExpired,
        HelperData $helperData,
        HeplerStatus $helperStatus,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Bss\QuoteExtension\Helper\CartHidePrice $cartHidePrice,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->coreRegistry = $registry;
        $this->helperExpired = $helperExpired;
        $this->helperData = $helperData;
        $this->helperStatus = $helperStatus;
        $this->productRepository = $productRepository;
        $this->cartHidePrice = $cartHidePrice;
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getQuoteExtension()
    {
        return $this->coreRegistry->registry('current_quote');
    }

    /**
     * Get request Quote
     *
     * @return mixed
     */
    public function getRequestQuote()
    {
        return $this->coreRegistry->registry('current_quote_extension');
    }

    /**
     * Retrieve format price
     *
     * @param int $value
     * @param int $storeId
     * @param string $currency
     * @return float|string
     */
    public function formatPrice($value, $storeId, $currency)
    {
        return $this->helperData->formatPrice($value, $storeId, $currency);
    }

    /**
     * Can show current subtotal
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function canShowCurrentSubtotal()
    {
        $manaQuote = $this->getRequestQuote();
        if ($manaQuote->getStatus() === Status::STATE_PENDING
            || $manaQuote->getStatus() === Status::STATE_CANCELED
            || $manaQuote->getStatus() === Status::STATE_REJECTED
        ) {
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
        return true;
    }

    /**
     * Get helper status
     *
     * @return HeplerStatus
     */
    public function getHelperStatus()
    {
        return $this->helperStatus;
    }
}
