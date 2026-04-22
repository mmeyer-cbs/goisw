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
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomPricing\Controller\Adminhtml\ProductPrice;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Update custom price for product
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Save extends SaveAction
{
    /**
     * @inheritDoc
     */
    protected function process($postData)
    {
        $productPriceId = $this->getRequest()->getPost("id", null);
        $priceType = $this->getRequest()->getPost("price_type");
        $priceValue = $this->getRequest()->getPost("price_value");

        if (!$productPriceId) {
            throw new NoSuchEntityException(
                __("The selected product price no longer exists.")
            );
        }
        $pPrice = $this->productPriceRepository->getById($productPriceId);
        $expectedPrice = $this->moduleHelper->prepareCustomPrice($priceType, $pPrice->getOriginPrice(), $priceValue);
        $pPrice->setPriceMethod($priceType);
        $pPrice->setPriceValue($priceValue);
        $pPrice->setCustomPrice($expectedPrice);
        $this->productPriceRepository->save($pPrice);
        return __("You saved custom price.");
    }
}
