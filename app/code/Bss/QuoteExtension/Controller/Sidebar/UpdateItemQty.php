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
namespace Bss\QuoteExtension\Controller\Sidebar;

use Bss\QuoteExtension\Model\QuoteExtension as CustomerQuoteExtension;
use Bss\QuoteExtension\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data;
use Magento\Quote\Api\Data\CartItemInterface;
use Psr\Log\LoggerInterface;

/**
 * Class UpdateItemQty
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class UpdateItemQty extends Action
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var \Bss\QuoteExtension\Helper\Data
     */
    protected $helperData;

    /**
     * @var CustomerQuoteExtension
     */
    protected $customerQuoteExtension;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * @param \Bss\QuoteExtension\Helper\Data $helperData
     * @param CustomerQuoteExtension $customerQuoteExtension
     * @param Context $context
     * @param LoggerInterface $logger
     * @param Data $jsonHelper
     * @codeCoverageIgnore
     */
    public function __construct(
        CheckoutSession  $checkoutSession,
        \Bss\QuoteExtension\Helper\Data $helperData,
        CustomerQuoteExtension  $customerQuoteExtension,
        Context $context,
        LoggerInterface $logger,
        Data $jsonHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->helperData = $helperData;
        $this->customerQuoteExtension = $customerQuoteExtension;
        $this->logger = $logger;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * Update item quote extension in mini quote
     *
     * @return Http
     */
    public function execute()
    {
        $itemId = (int)$this->getRequest()->getParam('item_id');
        $itemQty = $this->getRequest()->getParam('item_qty') * 1;

        try {
            $itemData = [$itemId => ['qty' => $this->helperData->normalize($itemQty)]];
            $this->checkQuoteItem($itemId);
            $this->customerQuoteExtension->updateItems($itemData)->save();
            return $this->jsonResponse();
        } catch (LocalizedException $e) {
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
     * @return Http
     */
    protected function jsonResponse($error = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($this->getResponseData($error))
        );
    }

    /**
     * Compile response data
     *
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

    /**
     * Check if required quote item exist
     *
     * @param int $itemId
     * @throws LocalizedException
     * @return $this
     */
    protected function checkQuoteItem($itemId)
    {
        $item = $this->checkoutSession->getQuoteExtension()->getItemById($itemId);
        if (!$item instanceof CartItemInterface) {
            throw new LocalizedException(__("The quote item isn't found. Verify the item and try again."));
        }
        return $this;
    }
}
