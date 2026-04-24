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
namespace Bss\QuoteExtension\Controller\Quote;

use Bss\QuoteExtension\Model\QuoteExtension as CustomerQuoteExtension;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Class ClearQuote
 *
 * @package Bss\QuoteExtension\Controller\Quote
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class ClearQuote extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Bss\QuoteExtension\Helper\Json
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var CustomerQuoteExtension
     */
    protected $quoteExtension;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $quoteExtensionSession;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * ClearQuote constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Bss\QuoteExtension\Helper\Json $jsonHelper
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param CustomerQuoteExtension $quoteExtension
     * @param \Magento\Checkout\Model\Session $quoteExtensionSession
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Bss\QuoteExtension\Helper\Json $jsonHelper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Bss\QuoteExtension\Model\QuoteExtension $quoteExtension,
        \Magento\Checkout\Model\Session $quoteExtensionSession,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
    ) {
        $this->logger = $logger;
        $this->jsonHelper = $jsonHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->quoteExtension = $quoteExtension;
        $this->quoteExtensionSession = $quoteExtensionSession;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\Response\Http|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        $itemId = (int)$this->getRequest()->getParam('item_id');
        $removeAll = $this->getRequest()->getParam('removeAll');
        try {
            if ($removeAll) {
                $this->clearQuote();
            } else {
                $this->checkQuoteItem($itemId);
                $this->removeQuoteItem($itemId);
            }
            return $this->jsonResponse();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $this->jsonResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return $this->jsonResponse($e->getMessage());
        }
    }

    /**
     * Compile JSON response
     *
     * @param string $error
     * @return \Magento\Framework\App\Response\Http
     */
    protected function jsonResponse($error = '')
    {
        $response = $this->getResponseData($error);

        return $this->getResponse()->representJson(
            $this->jsonHelper->serialize($response)
        );
    }

    /**
     * Check if required quote item exist
     *
     * @param int $itemId
     * @throws LocalizedException
     * @return $this
     */
    protected function checkQuoteItem($itemId)
    {
        $item = $this->quoteExtensionSession->getQuoteExtension()->getItemById($itemId);
        if (!$item instanceof CartItemInterface) {
            throw new LocalizedException(__("The quote item isn't found. Verify the item and try again."));
        }
        return $this;
    }

    /**
     * Remove quote item
     *
     * @param int $itemId
     * @return $this
     * @throws \Exception
     */
    protected function removeQuoteItem($itemId)
    {
        $this->quoteExtension->removeItem($itemId);
        $this->quoteExtension->save();
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function clearQuote()
    {
        $this->quoteExtension->truncate()->save();
        return $this;
    }

    /**
     * @param string $error
     * @return array
     */
    public function getResponseData($error = '')
    {
        if (empty($error)) {
            $response = [
                'success' => true,
            ];
        } else {
            $response = [
                'success' => false,
                'error_message' => $error,
            ];
        }
        return $response;
    }
}
