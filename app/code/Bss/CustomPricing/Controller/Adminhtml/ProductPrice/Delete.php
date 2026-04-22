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

use Bss\CustomPricing\Helper\Data;
use Bss\CustomPricing\Helper\IndexHelper;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Bss\CustomPricing\Api\ProductPriceRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * Button for deletion of customer address in admin
 */
class Delete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Bss_CustomPricing::custom_pricing_edit_rule';

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var IndexHelper
     */
    protected $indexHelper;

    /**
     * @var ProductPriceRepositoryInterface
     */
    private $productPriceRepository;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Action\Context $context
     * @param ProductPriceRepositoryInterface $productPriceRepository
     * @param JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     * @param Data $helper
     * @param IndexHelper $indexHelper
     */
    public function __construct(
        Action\Context $context,
        ProductPriceRepositoryInterface $productPriceRepository,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        Data $helper,
        IndexHelper $indexHelper
    ) {
        parent::__construct($context);
        $this->productPriceRepository = $productPriceRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->helper = $helper;
        $this->indexHelper = $indexHelper;
    }

    /**
     * Delete product price rule action
     *
     * @return Json
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $priceId = $this->getRequest()->getParam('id', false);
        $error = false;
        try {
            $this->productPriceRepository->deleteById($priceId);
            $this->indexHelper->cleanIndex($priceId);
            $this->helper->markInvalidateCache();
            $message = __('You deleted the product price.');
        } catch (\Exception $e) {
            $error = true;
            $message = __('We can\'t delete the product price right now.');
            $this->logger->critical($e);
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData(
            [
                'message' => $message,
                'error' => $error,
            ]
        );

        return $resultJson;
    }
}
