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
 * @package    Bss_HidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\HidePrice\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Bss\HidePrice\Model\Attribute\Source\HidePriceCustomer;

class CustomereditPost implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $typeList;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param TypeListInterface $typeList
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Cache\TypeListInterface $typeList,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->typeList = $typeList;
        $this->logger = $logger;
    }

    /**
     * Show alert flush cache after save customer
     *
     * @param EventObserver $observer
     * @return CustomereditPost
     */
    public function execute(EventObserver $observer)
    {
        try {
            $customerData = $observer->getEvent();
            $customAttribute = $customerData->getCustomerDataObject()->getCustomAttributes();
            $customAttributeOrig = $customerData->getOrigCustomerDataObject()
                ? $customerData->getOrigCustomerDataObject()->getCustomAttributes() : '';

            if (isset($customAttribute["bss_hide_pice_apply_customer"]) && $customAttributeOrig) {
                $newDataHidePrice = $customAttribute["bss_hide_pice_apply_customer"]->getValue();

                if (count($customerData->getOrigCustomerDataObject()->getCustomAttributes()) === 0) {
                    if ($newDataHidePrice != HidePriceCustomer::BSS_HIDE_PRICE_USE_CONFIG) {
                        $this->typeList->invalidate(
                            \Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER
                        );
                    }
                    return $this;
                }

                $oldDataHidePrice = $customAttributeOrig["bss_hide_pice_apply_customer"]->getValue();

                if ($oldDataHidePrice != $newDataHidePrice) {
                    $this->typeList->invalidate(
                        \Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER
                    );
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return $this;
    }
}
