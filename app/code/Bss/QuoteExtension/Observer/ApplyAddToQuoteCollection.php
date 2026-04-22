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
namespace Bss\QuoteExtension\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class ApplyAddToQuoteCollection
 *
 * @package Bss\QuoteExtension\Observer
 */
class ApplyAddToQuoteCollection implements ObserverInterface
{
    /**
     * Set Request4quote for product in collection
     *
     * @param EventObserver $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        $collection = $observer->getEvent()->getCollection();
        foreach ($collection as $product) {
            $data = $product->getData();
            if (!isset($data['url_key'])) {
                continue;
            }
            $product->setIsInCollection(true);
        }
        return $this;
    }
}
