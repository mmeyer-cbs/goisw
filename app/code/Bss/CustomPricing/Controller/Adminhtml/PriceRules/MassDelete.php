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
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Bss\CustomPricing\Model\ResourceModel\PriceRule\CollectionFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Quick delete multiple price rule
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class MassDelete extends \Magento\Backend\App\Action implements ActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_DELETE_PERMISSION_RESOURCE = "Bss_CustomPricing::price_rule_delete_rule";

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var PriceRuleRepositoryInterface
     */
    protected $priceRuleRepository;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var IndexHelper
     */
    protected $indexHelper;

    /**
     * MassDelete constructor.
     *
     * @param Action\Context $context
     * @param PriceRuleRepositoryInterface $priceRuleRepository
     * @param LoggerInterface $logger
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param Data $helper
     * @param IndexHelper $indexHelper
     */
    public function __construct(
        Action\Context $context,
        PriceRuleRepositoryInterface $priceRuleRepository,
        LoggerInterface $logger,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Data $helper,
        IndexHelper $indexHelper
    ) {
        parent::__construct($context);
        $this->priceRuleRepository = $priceRuleRepository;
        $this->logger = $logger;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;
        $this->indexHelper = $indexHelper;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $deletedCount = 0;
        if ($this->_authorization->isAllowed(self::ADMIN_DELETE_PERMISSION_RESOURCE)) {
            try {
                $collection = $this->filter->getCollection($this->collectionFactory->create());
                /** @var \Bss\CustomPricing\Api\Data\PriceRuleInterface $rule */
                foreach ($collection->getItems() as $rule) {
                    $ruleId = $rule->getId();
                    $this->priceRuleRepository->delete($rule);
                    $this->indexHelper->cleanIndex(null, $ruleId);
                    $deletedCount++;
                }

                if ($deletedCount > 0) {
                    $this->helper->markInvalidateCache();
                }
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been deleted.', $deletedCount)
                );
            } catch (CouldNotDeleteException $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage(__($e->getMessage()));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t mass delete price rules right now.')
                );
                $this->logger->critical($e);
            }
        } else {
            $this->messageManager->addErrorMessage(
                __('Sorry, you need permissions to %1.', __('delete selected Price Rules'))
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('custom_pricing/*/index');
        return $resultRedirect;
    }

    /**
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        return true;
    }
}
