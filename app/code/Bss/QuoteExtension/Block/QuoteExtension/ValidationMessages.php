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

use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;

/**
 * Quote cart validation messages block
 *
 * Class ValidationMessages
 *
 * @package Bss\QuoteExtension\Block\Quote
 */
class ValidationMessages extends \Magento\Checkout\Block\Cart\ValidationMessages
{
    /**
     * @var \Bss\QuoteExtension\Helper\QuoteExtension\Amount
     */
    protected $helperAmount;

    /**
     * ValidationMessages constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Message\Factory $messageFactory
     * @param \Magento\Framework\Message\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param InterpretationStrategyInterface $interpretationStrategy
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Framework\Locale\CurrencyInterface $currency
     * @param \Bss\QuoteExtension\Helper\QuoteExtension\Amount $helperAmount
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Message\Factory $messageFactory,
        \Magento\Framework\Message\CollectionFactory $collectionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        InterpretationStrategyInterface $interpretationStrategy,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\Locale\CurrencyInterface $currency,
        \Bss\QuoteExtension\Helper\QuoteExtension\Amount $helperAmount,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $messageFactory,
            $collectionFactory,
            $messageManager,
            $interpretationStrategy,
            $cartHelper,
            $currency,
            $data
        );
        $this->helperAmount = $helperAmount;
    }

    /**
     * Prepare Validate Messages
     *
     * @return \Magento\Checkout\Block\Cart\ValidationMessages|void
     */
    protected function _prepareLayout()
    {
        if ($this->helperAmount->getQuote()->getItemsCount()) {
            $this->isValidAmount();
            $this->addQuoteMessages();
            $this->addMessages($this->messageManager->getMessages(true));
        }
    }

    /**
     * Check minimum amount for customer group
     *
     * @return void
     */
    public function isValidAmount()
    {
        $quote = $this->helperAmount->getQuote();
        $addresses = $quote->getAllAddresses();
        $customerGroupId = $this->helperAmount->getCustomerGroup();
        if (!$this->helperAmount->validateQuoteAmount($addresses, $customerGroupId)) {
            $message = $this->helperAmount->getMessage($customerGroupId);
            if ($message) {
                $this->helperAmount->setInvalidAmount(true);
                $this->helperAmount->getMessageManager()->addNoticeMessage($message);
            }
        } else {
            $this->helperAmount->setInvalidAmount(false);
        }
    }
}
