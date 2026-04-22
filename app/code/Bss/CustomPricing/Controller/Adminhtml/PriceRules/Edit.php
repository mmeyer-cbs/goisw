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
use Bss\CustomPricing\Controller\RegistryConstants;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;

/**
 * Edit price rule controller
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Edit extends \Bss\CustomPricing\Controller\Adminhtml\PriceRule
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Bss_CustomPricing::custom_pricing_edit_rule';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Edit constructor.
     *
     * @param Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Bss\CustomPricing\Model\PriceRuleFactory $ruleFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param PriceRuleRepositoryInterface $priceRuleRepository
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Bss\CustomPricing\Model\PriceRuleFactory $ruleFactory,
        \Psr\Log\LoggerInterface $logger,
        PriceRuleRepositoryInterface $priceRuleRepository,
        PageFactory $resultPageFactory
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
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Rule edit action
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $this->_initRule();
            $model = $this->coreRegistry->registry(RegistryConstants::CURRENT_PRICE_RULE);
            $resultPage = $this->resultPageFactory->create();
            $resultPage->setActiveMenu("Bss_CustomPricing::price_rules");
            $resultPage->getConfig()->getTitle()->prepend(
                $model->getId() ? $model->getName() : __("Create New Rule")
            );
            $resultPage->getLayout()
                ->getBlock('bss_price_rule_form')
                ->setData('action', $this->getUrl('custom_pricing/priceRules/save'));

            $this->_addBreadcrumb(
                $model->getRuleId() ? __('Edit Rule') : __('Create Rule'),
                $model->getRuleId() ? __('Edit Rule') : __('Create Rule')
            );

            $resultPage->getConfig()->getTitle()->prepend(
                $model->getRuleId() ? $model->getName() : __('Create Rule')
            );
            return $resultPage;
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath('custom_pricing/priceRules');
        }
    }
}
