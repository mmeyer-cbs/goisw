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

use Bss\CustomPricing\Api\PriceRuleRepositoryInterface;
use Bss\CustomPricing\Api\ProductPriceRepositoryInterface;
use Bss\CustomPricing\Helper\IndexHelper;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class to remove price
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class RemovePrice extends SaveAction
{
    /**
     * @var IndexHelper
     */
    protected $indexHelper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        JsonFactory $resultJsonFactory,
        ProductPriceRepositoryInterface $productPriceRepository,
        PriceRuleRepositoryInterface $priceRuleRepository,
        \Psr\Log\LoggerInterface $logger,
        \Bss\CustomPricing\Helper\Data $moduleHelper,
        IndexHelper $indexHelper
    ) {
        parent::__construct($context, $resultJsonFactory, $productPriceRepository, $priceRuleRepository, $logger,
            $moduleHelper);
        $this->indexHelper = $indexHelper;
    }

    /**
     * @inheritDoc
     */
    protected function process($postData)
    {
        $productPriceId = $this->getRequest()->getParam("id", null);
        if (!$productPriceId) {
            throw new NoSuchEntityException(
                __("The selected product price no longer exists.")
            );
        }
        $pPrice = $this->productPriceRepository->getById($productPriceId);
        $pPrice->setPriceValue(null);
        $pPrice->setCustomPrice(null);
        $this->productPriceRepository->save($pPrice);

        $this->indexHelper->cleanIndex($pPrice->getId());
        return __("You removed custom price.");
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
