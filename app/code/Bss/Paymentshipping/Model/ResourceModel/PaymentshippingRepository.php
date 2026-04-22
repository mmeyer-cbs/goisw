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
 * @package    Bss_Paymentshipping
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\Paymentshipping\Model\ResourceModel;

use Bss\Paymentshipping\Api\PaymentshippingRepositoryInterface;
use Bss\Paymentshipping\Helper\Api;
use Bss\Paymentshipping\Helper\Data;
use Bss\Paymentshipping\Model\PaymentshippingFactory;
use Bss\Paymentshipping\Model\ResourceModel\Paymentshipping\CollectionFactory;
use Exception;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class HistoryRepository
 *
 * @package Bss\Paymentshipping\Model\ResourceModel
 */
class PaymentshippingRepository implements PaymentshippingRepositoryInterface
{
    /**
     * @var Data
     */
    protected $helperApi;

    /**
     * @var PaymentshippingFactory
     */
    protected $paymentShipping;

    /**
     * @var Paymentshipping
     */
    protected $paymentShippingResource;
    /**
     * @var CollectionProcessor
     */

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    protected $collectionProcessor;
    /**
     * @var Paymentshipping\CollectionFactory
     */
    protected $paymentShippingCollection;
    /**
     * @var SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * PaymentshippingRepository constructor.
     *
     * @param PaymentshippingFactory $paymentShipping
     * @param Paymentshipping $paymentShippingResource
     * @param SearchCriteriaBuilder $criteriaBuilder
     * @param CollectionProcessor $collectionProcessor
     * @param Paymentshipping\CollectionFactory $paymentShippingCollection
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        Api                   $helperApi,
        PaymentshippingFactory $paymentShipping,
        Paymentshipping                                   $paymentShippingResource,
        SearchCriteriaBuilder                             $criteriaBuilder,
        CollectionProcessor                               $collectionProcessor,
        CollectionFactory                                 $paymentShippingCollection,
        SearchResultsInterfaceFactory                     $searchResultsFactory
    ) {
        $this->helperApi = $helperApi;
        $this->paymentShipping = $paymentShipping;
        $this->paymentShippingResource = $paymentShippingResource;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->collectionProcessor = $collectionProcessor;
        $this->paymentShippingCollection = $paymentShippingCollection;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @inheritdoc
     */
    public function save($paymentShipping)
    {
        try {
            $this->paymentShippingResource->save($paymentShipping);
            return $paymentShipping;
        } catch (Exception $exception) {
            throw new CouldNotSaveException(
                __(
                    'Could not save with type: %1, website_id: %2, method: %3',
                    $paymentShipping->getType(),
                    $paymentShipping->getWebsiteId(),
                    $paymentShipping->getMethod()
                )
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $collection = $this->paymentShippingCollection->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function getLastItem(SearchCriteriaInterface $criteria)
    {
        $collection = $this->paymentShippingCollection->create();
        $this->collectionProcessor->process($criteria, $collection);
        return $collection->getLastItem();
    }

    /**
     * @inheritdoc
     */
    public function getListPaymentShipping($type, $customerGroupId)
    {
        $searchCriteriaBuilder = $this->criteriaBuilder->addFilter('type', ['eq' => $type]);
        $searchCriteriaBuilder->addFilter('group_ids', "%" . $customerGroupId . "%", "like");
        $searchCriteria = $searchCriteriaBuilder->create();
        $listPaymentAllowGroup = [];
        $listPayment = $this->getList($searchCriteria);
        foreach ($listPayment->getItems() as $payment) {
            if ($payment->getGroupIds()) {
                $allowedGroups = explode(',', $payment->getGroupIds());
                if (in_array($customerGroupId, $allowedGroups)) {
                    $listPaymentAllowGroup[] = $payment;
                }
            }
        }
        return $listPaymentAllowGroup;
    }

    /**
     * @inheritdoc
     */
    public function savePaymentShippings($paymentShippings)
    {
        $result = [];
        foreach ($paymentShippings as $paymentShipping) {
            $searchCriteriaBuilder = $this->criteriaBuilder->addFilter('type', ['eq' => $paymentShipping->getType()])
                ->addFilter('method', ['eq' => $paymentShipping->getMethod()])
                ->addFilter('website_id', $paymentShipping->getWebsiteId());
            $searchCriteria = $searchCriteriaBuilder->create();
            $item = $this->getLastItem($searchCriteria);
            try {
                if ($item->getEntityId()) {
                    $item->setGroupIds($paymentShipping->getGroupIds());
                    $item->save();
                } else {
                    $this->save($paymentShipping);
                }
                $result[] = [
                    "message" => __("Success")->render(),
                    "status" => true
                ];
            } catch (Exception $exception) {
                $result[] = [
                    "message" => __("%1", $exception->getMessage())->render(),
                    "status" => false
                ];
            }
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function checkAllow($type, $websiteId, $method, $customerGroupId, $storeId)
    {
        $searchCriteriaBuilder = $this->criteriaBuilder->addFilter('type', ['eq' => $type])
            ->addFilter('group_ids', "%" . $customerGroupId . "%", "like")
            ->addFilter('website_id', ['eq' => $websiteId])
            ->addFilter("method", $method);
        $searchCriteria = $searchCriteriaBuilder->create();
        $item = $this->getLastItem($searchCriteria);
        if ($item->getId()) {
            $allowedGroups = explode(',', $item->getGroupIds() ?? "");
            if (in_array($customerGroupId, $allowedGroups)) {
                return [
                    "message" => __("Allow")->render(),
                    "status" => true
                ];
            }
        }
        $title = $this->helperApi->getTitle($type, $method, $storeId);
        return [
            "message" => __("You are not allowed to use '%1'", $title)->render(),
            "status" => false
        ];
    }
}
