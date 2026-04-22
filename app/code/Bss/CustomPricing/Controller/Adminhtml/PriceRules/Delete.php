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

namespace Bss\CustomPricing\Controller\Adminhtml\PriceRules;

use Bss\CustomPricing\Api\PriceRuleRepositoryInterface;
use Bss\CustomPricing\Helper\Data;
use Bss\CustomPricing\Helper\IndexHelper;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\CouldNotDeleteException;

/**
 * Delete price rule action
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Delete extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = "Bss_CustomPricing::custom_pricing_delete_rule";

    /**
     * @var PriceRuleRepositoryInterface
     */
    protected $priceRuleRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var IndexHelper
     */
    protected $indexHelper;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Delete constructor.
     *
     * @param Action\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param PriceRuleRepositoryInterface $priceRuleRepository
     * @param IndexHelper $indexHelper
     * @param Data $helper
     */
    public function __construct(
        Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        PriceRuleRepositoryInterface $priceRuleRepository,
        IndexHelper $indexHelper,
        Data $helper
    ) {
        parent::__construct($context);
        $this->priceRuleRepository = $priceRuleRepository;
        $this->logger = $logger;
        $this->indexHelper = $indexHelper;
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $redirect = $this->resultRedirectFactory->create();
        try {
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $this->priceRuleRepository->deleteById($id);
                $this->messageManager->addErrorMessage(__("You deleted the rule."));
            }
            $this->indexHelper->cleanIndex(null, $id);
            $this->helper->markInvalidateCache();
            $redirect->setPath("*/");
        } catch (CouldNotDeleteException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $redirect->setPath("*/*/edit", ["id" => $this->getRequest()->getParam("id")]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while deleting the rule. Please review the error log.')
            );
            $this->logger->critical($e);
            $redirect->setPath("*/*/edit", ["id" => $this->getRequest()->getParam("id")]);
        }
        return $redirect;
    }
}
