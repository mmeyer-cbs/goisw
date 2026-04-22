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
use Magento\Backend\App\Action;
use Bss\CustomPricing\Controller\Adminhtml\PriceRule;

/**
 * Class Add to get view of create new price rule
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class NewAction extends PriceRule
{
    /**
     * @var Action\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    private $resultForwardFactory;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = "Bss_CustomPricing::custom_pricing_new_rule";

    /**
     * New action constructor.
     *
     * @param Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Bss\CustomPricing\Model\PriceRuleFactory $ruleFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param PriceRuleRepositoryInterface $priceRuleRepository
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Bss\CustomPricing\Model\PriceRuleFactory $ruleFactory,
        \Psr\Log\LoggerInterface $logger,
        PriceRuleRepositoryInterface $priceRuleRepository,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
    ) {
        parent::__construct(
            $context,
            $coreRegistry,
            $fileFactory,
            $dateFilter,
            $ruleFactory,
            $priceRuleRepository,
            $logger
        );
        $this->resultForwardFactory = $resultForwardFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $forward = $this->resultForwardFactory->create();
        $forward->forward('edit');
        return $forward;
    }
}
