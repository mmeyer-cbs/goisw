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

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class to remove selected product's custom price through mass action
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class MassRemovePrice extends MultipleUpdatePrice
{
    /**
     * @inheritDoc
     */
    protected function process($postData)
    {
        $productPrices = $this->prepareProductPriceCollection($postData);
        $removePriceIds = [];
        foreach ($productPrices->getItems() as $cPrice) {
            $cPrice->setPriceValue(null);
            $cPrice->setCustomPrice(null);
            $cPrice->setShouldReindex(false);
            $this->productPriceRepository->save($cPrice);


            $removePriceIds[] = $cPrice->getId();
        }

        $this->indexerHelper->cleanIndex($removePriceIds);

        return __("A total of %1 record(s) have been updated.", count($removePriceIds));
    }

    /**
     * No need to validate post data
     *
     * @return array
     */
    protected function validatePostData()
    {
        return $this->getRequest()->getPost();
    }
}
