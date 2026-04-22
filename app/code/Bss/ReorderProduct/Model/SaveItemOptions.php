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
 * @package    Bss_ReorderProduct
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ReorderProduct\Model;

/**
 * Class AddOptionItem
 *
 * @package Bss\ReorderProduct\Observer
 */
class SaveItemOptions
{
    /**
     * @var \Bss\ReorderProduct\Model\ResourceModel\SaveReorderItemOptions
     */
    protected $saveReorderItemOptions;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Serialize
     */
    protected $serializer;

    /**
     * @var \Bss\ReorderProduct\Helper\Data
     */
    protected $helper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * SaveItemOptions constructor.
     * @param ResourceModel\SaveReorderItemOptions $saveReorderItemOptions
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serializer
     * @param \Bss\ReorderProduct\Helper\Data $helper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Bss\ReorderProduct\Model\ResourceModel\SaveReorderItemOptions $saveReorderItemOptions,
        \Magento\Framework\Serialize\Serializer\Serialize $serializer,
        \Bss\ReorderProduct\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->saveReorderItemOptions = $saveReorderItemOptions;
        $this->serializer = $serializer;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * Process save option
     *
     * @param \Magento\Sales\Model\Order $order
     */
    public function processData($order)
    {
        $ignoreBuyrequest = $this->helper->getIgnoreBuyRquestParam();
        $ignoreBuyrequest = $ignoreBuyrequest ? explode(',', $ignoreBuyrequest) : $ignoreBuyrequest;
        $ignorePrams = ['qty', 'uenc', 'form_key', 'item', 'wishlist_id', 'bss_current_url', 'original_qty'];
        if ($ignoreBuyrequest) {
            $ignorePrams = array_merge($ignorePrams, $ignoreBuyrequest);
        }
        try {
            foreach ($order->getAllVisibleItems() as $item) {
                $itemOptionValue = $item->getProductOptions();
                if (!isset($itemOptionValue['info_buyRequest']['product'])) {
                    $product['product'] = $item->getProductId();
                    $itemOptionValue['info_buyRequest'] = $product + $itemOptionValue['info_buyRequest'];
                }
                foreach ($ignorePrams as $key) {
                    unset($itemOptionValue['info_buyRequest'][$key]);
                }
                $newItemOptionValue = array_filter($itemOptionValue['info_buyRequest'], function ($optionValue) {
                    return !empty($optionValue);
                });

                $data = $this->serializer->serialize($newItemOptionValue);
                $item->setReorderItemOptions($data);
                $this->saveReorderItemOptions->saveItemsOption($item->getItemId(), $data);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
