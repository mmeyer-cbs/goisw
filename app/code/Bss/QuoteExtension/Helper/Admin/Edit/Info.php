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

use Magento\Eav\Model\AttributeDataFactory;

/**
 * Class Info
 *
 * @package Bss\QuoteExtension\Helper\Admin\Edit
 */
class Info extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\ToOrderAddress $quoteAddressToOrderAddress
     */
    protected $quoteAddressToOrderAddress;

    /**
     * @var \Magento\Sales\Model\Order\Address\Renderer
     */
    protected $addressRenderer;

    /**
     * Group service
     *
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * EditInfo constructor.
     *
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Quote\Model\Quote\Address\ToOrderAddress $quoteAddressToOrderAddress
     * @param \Magento\Sales\Model\Order\Address\Renderer $addressRenderer
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Quote\Model\Quote\Address\ToOrderAddress $quoteAddressToOrderAddress,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->quoteAddressToOrderAddress = $quoteAddressToOrderAddress;
        $this->addressRenderer = $addressRenderer;
        $this->groupRepository = $groupRepository;
    }

    /**
     * Get Order Ids from quote Id
     *
     * @param int $quoteId
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrderIds($quoteId)
    {
        $orderIds = $this->orderCollectionFactory->create()->addFieldToSelect(
            [
                'increment_id',
                'entity_id'
            ]
        )->addFieldToFilter(
            'quote_id',
            $quoteId
        )->setOrder(
            'increment_id',
            'asc'
        );
        return $orderIds;
    }

    /**
     * Convert Address
     *
     * @param string $type
     * @param $quote
     * @return string|null
     */
    public function formatAddress($type, $quote)
    {
        if ($type == 'shipping') {
            $salesAddress = $this->quoteAddressToOrderAddress->convert($quote->getShippingAddress());
            if (!$salesAddress->getFirstname()) {
                return null;
            }
            return $this->addressRenderer->format($salesAddress, 'html');
        }
        $salesAddress = $this->quoteAddressToOrderAddress->convert($quote->getBillingAddress());
        if (!$salesAddress->getFirstname()) {
            return null;
        }
        return $this->addressRenderer->format($salesAddress, 'html');
    }

    /**
     * Get Customer group code by customer group id
     *
     * @param int $customerGroupId
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCustomerGroupByCode($customerGroupId)
    {
        $this->groupRepository->getById($customerGroupId)->getCode();
    }

    /**
     * Return eav attribute output html
     *
     * @return string
     */
    public function returnOutPutHtml()
    {
        return AttributeDataFactory::OUTPUT_FORMAT_HTML;
    }

    /**
     * Return store view scope
     *
     * @return string
     */
    public function returnScopeStoreView()
    {
        return \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    }
}
