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
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\QuoteExtension\Controller\Quote;

use Bss\QuoteExtension\Helper\Data;
use Bss\QuoteExtension\Helper\Json as JsonHelper;
use Bss\QuoteExtension\Helper\Mail;
use Bss\QuoteExtension\Helper\QuoteExtension\Address;
use Bss\QuoteExtension\Model\Config\Source\Status;
use Bss\QuoteExtension\Model\ManageQuote;
use Bss\QuoteExtension\Model\QuoteItem;
use Bss\QuoteExtension\Model\QuoteItemFactory;
use Bss\QuoteExtension\Model\QuoteVersion;
use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class ViewSubmit
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class ViewSubmit extends Action
{
    /**
     * @var int
     */
    protected $checkNotAddressCusomter = 0;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var ManageQuote
     */
    protected $manageQuote;

    /**
     * @var QuoteItemFactory
     */
    protected $quoteItemFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var QuoteVersion
     */
    protected $quoteVersion;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @var Address
     */
    protected $helperQuoteAddress;

    /**
     * @var Mail
     */
    protected $mailHelper;

    /**
     * ViewSubmit constructor.
     *
     * @param Context $context
     * @param Validator $formKeyValidator
     * @param CartRepositoryInterface $quoteRepository
     * @param ManageQuote $manageQuote
     * @param QuoteItemFactory $quoteItemFactory
     * @param Data $helper
     * @param Mail $mailHelper
     * @param QuoteVersion $quoteVersion
     * @param JsonHelper $jsonHelper
     * @param Address $helperQuoteAddress
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context                 $context,
        Validator               $formKeyValidator,
        CartRepositoryInterface $quoteRepository,
        ManageQuote             $manageQuote,
        QuoteItemFactory        $quoteItemFactory,
        Data                    $helper,
        Mail                    $mailHelper,
        QuoteVersion            $quoteVersion,
        JsonHelper              $jsonHelper,
        Address                 $helperQuoteAddress
    ) {
        parent::__construct($context);
        $this->formKeyValidator = $formKeyValidator;
        $this->quoteRepository = $quoteRepository;
        $this->manageQuote = $manageQuote;
        $this->quoteItemFactory = $quoteItemFactory;
        $this->helper = $helper;
        $this->quoteVersion = $quoteVersion;
        $this->jsonHelper = $jsonHelper;
        $this->helperQuoteAddress = $helperQuoteAddress;
        $this->mailHelper = $mailHelper;
    }

    /**
     * Resubmit quote extension
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if (!$this->formKeyValidator->validate($this->getRequest()) && !isset($params['request_entity_id'])) {
            return $this->resultRedirectFactory->create()->setPath('*/*/history');
        }

        try {
            $manageQuote = $this->manageQuote->load($params['request_entity_id']);
            if (!$manageQuote->getQuoteId()) {
                $this->messageManager->addErrorMessage(__('We can\'t find a quote.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/history');
            }
            if (!$this->isCanResubmit($manageQuote->getStatus())) {
                return $this->returnNotResubmitQuoteE($params, "This action can not be done.");
            }

            $quote = $this->quoteRepository->get($manageQuote->getQuoteId());
            $this->manageQuote->load($params['request_entity_id']);
            $data = $this->updateItems($params['quote'], $quote, $manageQuote);
            if ($this->checkChangeShippingInfo($params)) {
                $this->saveShippingInformation($params, $quote);
                if ($this->checkNotAddressCusomter) {
                    return $this->returnNotResubmitQuoteE($params, "This Quote can't update.");
                }
            } else {
                $quote->getShippingAddress()
                    ->setCollectShippingRates(true)
                    ->collectShippingRates();
            }


            $this->saveQEComment($manageQuote, $params, $data);

            $quote->collectTotals();
            $this->quoteRepository->save($quote);
            $manageQuote->setMoveCheckout(0);
            $manageQuote->save();
        } catch (Exception $e) {
            return $this->returnNotResubmitQuoteE($params, $e->getMessage());
        }
        $this->messageManager->addSuccessMessage(__('You updated the quote'));
        $resultRedirect = $this->resultRedirectFactory->create();
        if (isset($params['token'])) {
            return $resultRedirect->setPath(
                'quoteextension/quote/view',
                [
                    'quote_id' => $params['request_entity_id'],
                    'token' => $params['token']
                ]
            );
        }
        return $resultRedirect->setPath(
            'quoteextension/quote/view',
            [
                'quote_id' => $params['request_entity_id']
            ]);
    }

    /**
     * Check change shipping info into save shipping address
     *
     * @param array $params
     * @return bool
     */
    public function checkChangeShippingInfo($params)
    {
        if (isset($params['change_shipping_info'])
            && $params['change_shipping_info']
            && $this->helperQuoteAddress->isRequiredAddress()
        ) {
            return true;
        }
        return false;
    }

    /**
     * Save quote extension comment
     *
     * @param ManageQuote $manageQuote
     * @param array $params
     * @param array $data
     * @throws Exception
     */
    public function saveQEComment($manageQuote, $params, $data)
    {
        if ($params['customer_note']) {
            $data['comment'] = $params['customer_note'];
            $data['quote_id_not_comment'] = $this->manageQuote->getBackendQuoteId();
            $manageQuote->setVersion($manageQuote->getVersion() + 1);
            $this->quoteVersion->setData($data);
            $this->quoteVersion->save();
        }
    }

    /**
     * Function Update Items
     *
     * @param array $data
     * @param CartRepositoryInterface $quote
     * @param ManageQuote $requestQuote
     * @return array
     * @throws Exception
     */
    protected function updateItems($data, $quote, $requestQuote)
    {
        $versionData = [];
        foreach ($data as $itemId => $info) {
            $item = $quote->getItemById($itemId);
            if (!$item) {
                continue;
            }

            /* Load request quote item */
            $requestQuoteItem = $this->getRequestQuoteItem($itemId);

            $dataItem = [];
            $dataItem['price'] = $this->getPriceByItem($item);
            $dataItem['name'] = $item->getName();
            $dataItem['sku'] = $item->getSku();
            $dataItem['comment'] = $requestQuoteItem->getComment();
            $dataItem['qty'] = $item->getQty();
            $versionData[$itemId] = $dataItem;

            $info['qty'] = (double)$info['qty'];
            $item->setQty($info['qty']);
            if (isset($info['description'])) {
                if ($requestQuoteItem->getId()) {
                    $requestQuoteItem->setComment($info['description']);
                    $this->saveRequestQuoteItem($requestQuoteItem);
                } else {
                    $data['item_id'] = $itemId;
                    $data['comment'] = $info['description'];
                    $requestQuoteItem->setData($data);
                    $this->saveRequestQuoteItem($requestQuoteItem);
                }
            }
        }

        $data = [
            'quote_id' => $requestQuote->getId(),
            'version' => $requestQuote->getVersion() + 1,
            'status' => $requestQuote->getStatus(),
            'log' => $this->jsonHelper->serialize($versionData)
        ];

        return $data;
    }

    /**
     * Get price by item
     *
     * @param QuoteItem $item
     * @return array
     */
    public function getPriceByItem($item)
    {
        if (!$item->getCustomPrice()) {
            $price = [
                'price' => $item->getPrice(),
                'base_price' => $item->getBasePrice(),
                'price_incl_tax' => $item->getPriceInclTax(),
                'base_price_incl_tax' => $item->getBasePriceInclTax()
            ];
        } else {
            $price = [
                'customprice' => $item->getCustomPrice(),
                'price' => $item->getPrice(),
                'base_price' => $item->getBasePrice(),
                'price_incl_tax' => $item->getPriceInclTax(),
                'base_price_incl_tax' => $item->getBasePriceInclTax()
            ];
        }
        return $price;
    }

    /**
     * Save Shipping information
     *
     * @param array $data
     * @param CartRepositoryInterface $quote
     * @throws Exception
     */
    protected function saveShippingInformation($data, $quote)
    {
        $address = $data['address'];
        if (isset($data['address']['customer_address_id'])) {
            $customerAddressId = $data['address']['customer_address_id'];
            $address = $this->helperQuoteAddress->getCustomerAddress($customerAddressId)->getData();
            if ($quote->getCustomerId() && $quote->getCustomerId() != $address["parent_id"]) {
                $this->checkNotAddressCusomter = 1;
                return;
            }
        }
        if ($quote) {
            $quote->getShippingAddress()->addData($address);
            if (isset($data['shipping_method'])) {
                $quote->getShippingAddress()
                    ->setCollectShippingRates(true)
                    ->collectShippingRates()
                    ->setShippingMethod($data['shipping_method']);
            }
        }
    }

    /**
     * Get Request Quote Item
     *
     * @param int $itemId
     * @return QuoteItem
     */
    protected function getRequestQuoteItem($itemId)
    {
        return $this->quoteItemFactory->create()->load($itemId, 'item_id');
    }

    /**
     * Save Request Quote Item
     *
     * @param QuoteItem $requestQuoteItem
     * @return mixed
     * @throws Exception
     */
    protected function saveRequestQuoteItem($requestQuoteItem)
    {
        try {
            return $requestQuoteItem->save();
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $this;
    }

    /**
     * Is can resubmit
     *
     * @param string $status
     * @return bool
     */
    private function isCanResubmit($status)
    {
        $disableResubmit = $this->helper->disableResubmit();
        if (!$disableResubmit) {
            $statusCanEdit = [
                Status::STATE_UPDATED,
                Status::STATE_REJECTED,
                Status::STATE_EXPIRED
            ];
        } else {
            $statusCanEdit = [
                Status::STATE_UPDATED
            ];
        }
        if (!in_array($status, $statusCanEdit)) {
            return false;
        }
        return true;
    }

    /**
     * Return page current when customer not resubmit quote extension
     *
     * @param array $params
     * @param string $message
     * @return Redirect
     */
    public function returnNotResubmitQuoteE($params, $message)
    {
        $this->messageManager->addErrorMessage(__("%1", $message));
        return $this->resultRedirectFactory
            ->create()
            ->setPath('*/*/view/quote_id/' . $params['request_entity_id']);
    }
}
