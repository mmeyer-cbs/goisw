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

namespace Bss\CustomPricing\Controller\Adminhtml;

use Bss\CustomPricing\Api\PriceRuleRepositoryInterface;
use Bss\CustomPricing\Controller\RegistryConstants;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Abstract price rule controller
 */
abstract class PriceRule extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = "Bss_CustomPricing::custom_pricing";

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $dateFilter;

    /**
     * @var \Bss\CustomPricing\Model\PriceRule
     */
    protected $ruleFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var PriceRuleRepositoryInterface
     */
    protected $priceRuleRepository;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Bss\CustomPricing\Model\PriceRuleFactory $ruleFactory
     * @param PriceRuleRepositoryInterface $priceRuleRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Bss\CustomPricing\Model\PriceRuleFactory $ruleFactory,
        PriceRuleRepositoryInterface $priceRuleRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->fileFactory = $fileFactory;
        $this->dateFilter = $dateFilter;
        $this->ruleFactory = $ruleFactory;
        $this->logger = $logger;
        $this->priceRuleRepository = $priceRuleRepository;
    }

    /**
     * Initiate rule
     *
     * @return void
     * @throws NoSuchEntityException
     */
    protected function _initRule()
    {
        $id = (int)$this->getRequest()->getParam('id');

        if (!$id && $this->getRequest()->getParam('rule_id')) {
            $id = (int)$this->getRequest()->getParam('rule_id');
        }

        if ($id) {
            $model = $this->priceRuleRepository->getById($id);
            if (!$model->getRuleId()) {
                throw new NoSuchEntityException(__('This price rule no longer exists.'));
            }
        } else {
            $model = $this->ruleFactory->create();
        }

        // set entered data if was error when we do save
        $data = $this->_session->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $model->getConditions()->setFormName("bss_price_rule_form");
        $model->getConditions()->setJsFormObject(
            $model->getConditionsFieldSetId($model->getConditions()->getFormName() . '_product_')
        );
        $model->getCustomerConditions()->setJsFormObject(
            $model->getConditionsFieldSetId($model->getCustomerConditions()->getFormName() . '_customer_')
        );
        $this->coreRegistry->register(
            RegistryConstants::CURRENT_PRICE_RULE,
            $model
        );
    }
}
