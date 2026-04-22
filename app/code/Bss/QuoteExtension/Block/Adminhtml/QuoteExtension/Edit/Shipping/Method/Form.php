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
namespace Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit\Shipping\Method;

use Bss\QuoteExtension\Model\Config\Source\Status;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Adminhtml quote extension view shipping method form block
 *
 * Class Form
 * @package Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit\Shipping\Method
 */
class Form extends \Magento\Sales\Block\Adminhtml\Order\Create\Shipping\Method\Form
{
    /**
     * Helper
     *
     * @var \Bss\QuoteExtension\Helper\Data
     */
    protected $helper;

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Quote Repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Form constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Bss\QuoteExtension\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Framework\Registry $registry,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Bss\QuoteExtension\Helper\Data $helper,
        array $data
    ) {
        $this->registry = $registry;
        $this->quoteRepository = $quoteRepository;
        $this->helper = $helper;
        parent::__construct(
            $context,
            $sessionQuote,
            $orderCreate,
            $priceCurrency,
            $taxData,
            $data
        );
        $this->registry = $registry;
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('quote_extension_quote_view_shipping_method_form');
        if (!$this->canShowButtonAction()) {
            $this->setTemplate("Bss_QuoteExtension::quoteextension/view/shipping/method/info.phtml");
        } else {
            $this->setTemplate("Bss_QuoteExtension::quoteextension/view/shipping/method/form.phtml");
        }
    }

    /**
     * Retrieve quote model object
     *
     * @return \Magento\Quote\Model\Quote|mixed
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

    /**
     * Get request Quote
     *
     * @return mixed
     */
    public function getManageQuote()
    {
        return $this->registry->registry('quoteextension_quote');
    }

    /**
     * Get the shipping price
     *
     * @param float $price
     * @param bool $flag
     * @return float
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPrice($price, $flag)
    {
        if ($this->getAddress()->getQuote()) {
            $quote = $this->getAddress()->getQuote();
        } else {
            $quote = $this->getQuote();
        }

        return $this->_taxData->getShippingPrice(
            $price,
            $flag,
            $this->getAddress(),
            null,
            $quote->getStore()
        );
    }

    /**
     * Is quotation shipping
     *
     * @param string $code
     * @return bool
     */
    public function isQuoteShipping($code)
    {
        return $code == \Bss\QuoteExtension\Model\Carrier\QuoteExtensionShipping::CODE;
    }

    /**
     * Return back can show button action.
     *
     * @return bool
     */
    protected function canShowButtonAction()
    {
        if (!$mageQuote = $this->getManageQuote()) {
            return true;
        }
        $quoteStatus = $mageQuote->getStatus();
        $ignore = [Status::STATE_CANCELED, Status::STATE_ORDERED , Status::STATE_REJECTED, Status::STATE_UPDATED];
        if (in_array($quoteStatus, $ignore)) {
            return false;
        }
        return true;
    }
}
