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

use Bss\CustomPricing\Api\ProductPriceRepositoryInterface;
use Bss\CustomPricing\Api\PriceRuleRepositoryInterface;
use Bss\CustomPricing\Model\Config\Source\PriceTypeOption;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Setup\Console\InputValidationException;

/**
 * Save abstract action
 */
abstract class SaveAction extends \Magento\Backend\App\Action
{
    const UPDATE_PRODUCT_PRICE_RESOURCE = "Bss_CustomPricing::custom_pricing_update_product_price";

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var ProductPriceRepositoryInterface
     */
    protected $productPriceRepository;

    /**
     * @var PriceRuleRepositoryInterface
     */
    protected $priceRuleRepository;

    /**
     * @var \Bss\CustomPricing\Helper\Data
     */
    protected $moduleHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Save constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ProductPriceRepositoryInterface $productPriceRepository
     * @param PriceRuleRepositoryInterface $priceRuleRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Bss\CustomPricing\Helper\Data $moduleHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        JsonFactory $resultJsonFactory,
        ProductPriceRepositoryInterface $productPriceRepository,
        PriceRuleRepositoryInterface $priceRuleRepository,
        \Psr\Log\LoggerInterface $logger,
        \Bss\CustomPricing\Helper\Data $moduleHelper
    ) {
        $this->logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productPriceRepository = $productPriceRepository;
        $this->priceRuleRepository = $priceRuleRepository;
        $this->moduleHelper = $moduleHelper;
        parent::__construct($context);
    }

    /**
     * Process saving data
     *
     * @param array $postData
     *
     * @return string
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    abstract protected function process($postData);

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $gotError = false;
        try {
            if (!$this->_authorization->isAllowed(self::UPDATE_PRODUCT_PRICE_RESOURCE)
            ) {
                throw new CouldNotSaveException(
                    __("Sorry, you need permissions to %1.", __("update the price rule"))
                );
            }
            $postData = $this->validatePostData();
            $message = $this->process($postData);

            $this->moduleHelper->markInvalidateCache();
        } catch (NoSuchEntityException $e) {
            $gotError = true;
            $message = $e->getMessage();
        } catch (CouldNotSaveException $e) {
            $gotError = true;
            $message = $e->getMessage();
        } catch (InputValidationException $e) {
            $gotError = true;
            $message = $e->getMessage();
        } catch (\Exception $e) {
            $gotError = true;
            $this->logger->critical($e);
            $message = __("Something went wrong! Please check the log.");
        }

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData([
            'message' => $message,
            'error' => $gotError
        ]);

        return $resultJson;
    }

    /**
     * Validate the post data
     *
     * @return array
     * @throws InputValidationException
     */
    protected function validatePostData()
    {
        $postData = $this->getRequest()->getPost();

        // require entry
        if (!isset($postData["price_value"]) || empty($postData["price_value"])) {
            throw new InputValidationException(
                __("Please input definition of custom price.")
            );
        }
        // is valid number | not negative
        if (!is_numeric($postData["price_value"]) || (float)$postData["price_value"] < 0) {
            throw new InputValidationException(
                __("Please input correct type number.")
            );
        }
        return $postData;
    }
}
