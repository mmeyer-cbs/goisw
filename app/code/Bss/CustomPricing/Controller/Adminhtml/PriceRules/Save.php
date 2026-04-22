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
use Bss\CustomPricing\Helper\PriceRuleSave;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * The place where save the price rule
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Bss\CustomPricing\Controller\Adminhtml\PriceRule
{
    /**
     * @var \Bss\CustomPricing\Helper\IndexHelper
     */
    protected $indexHelper;

    /**
     * @var PriceRuleSave
     */
    private $priceRuleSave;

    /**
     * Save constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Bss\CustomPricing\Model\PriceRuleFactory $ruleFactory
     * @param PriceRuleRepositoryInterface $priceRuleRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param PriceRuleSave $priceRuleSave
     * @param \Bss\CustomPricing\Helper\IndexHelper $indexHelper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Bss\CustomPricing\Model\PriceRuleFactory $ruleFactory,
        PriceRuleRepositoryInterface $priceRuleRepository,
        \Psr\Log\LoggerInterface $logger,
        PriceRuleSave $priceRuleSave,
        \Bss\CustomPricing\Helper\IndexHelper $indexHelper
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
        $this->priceRuleSave = $priceRuleSave;
        $this->indexHelper = $indexHelper;
    }

    /**
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $redirect = $this->resultRedirectFactory->create();

        if (!$this->getRequest()->getPostValue()) {
            return $redirect->setPath('custom_pricing/*/');
        }

        $data = $this->getRequest()->getPostValue();
        $data = $this->prepareData($data);
        try {
            $model = $this->ruleFactory->create();
            $id = $this->getRequest()->getParam('general_information')["id"] ?? null;
            if ($id) {
                $model = $this->priceRuleRepository->getById($id);
            } else {
                unset($data["id"]);
            }
            $validateResult = $model->validateData(new \Magento\Framework\DataObject($data));
            if ($validateResult !== true) {
                foreach ($validateResult as $errorMessage) {
                    $this->messageManager->addErrorMessage($errorMessage);
                }
                $this->_session->setPageData($data);
                $this->_session->setFormData($data);
                $redirect->setPath('custom_pricing/*/edit', ['id' => $model->getId()]);
                return $redirect;
            }
            $model->loadPost($data);
            $this->_session->setPageData($model->getData());
            $this->priceRuleRepository->save($model);

            /* validate and push products to product price tab */
            $this->saveProductPrice($model);

            /* validate and push customers to applied customers tab */
            $this->saveAppliedCustomers($model);

            $this->messageManager->addSuccessMessage(__('You saved the rule.'));

            $this->indexHelper->reindexByRule((int) $model->getRuleId());
            $this->priceRuleSave->markInvalidateCache();

            $this->_session->setPageData(false);
            if ($this->getRequest()->getParam('back')) {
                $redirect->setPath('custom_pricing/*/edit', ['id' => $model->getId()]);
                return $redirect;
            }
            $redirect->setPath('custom_pricing/*/');
            return $redirect;
        } catch (CouldNotSaveException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $id = $this->getRequest()->getParam('general_information')["id"] ?? null;
            if (!empty($id)) {
                $this->_session->setFormData($data);
                $redirect->setPath('custom_pricing/*/edit', ['id' => $id]);
            } else {
                $redirect->setPath('custom_pricing/*/new');
            }
            return $redirect;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('Something went wrong while saving the rule data. Please review the error log.')
            );
            $this->logger->critical($e);
            $data = !empty($data) ? $data : [];
            $this->_session->setPageData($data);
            $this->_session->setFormData($data);
            $redirect->setPath(
                'custom_pricing/*/edit',
                ['id' => $this->getRequest()->getParam('general_information')["id"] ?? null]
            );
            return $redirect;
        }
    }

    /**
     * Save custom price for product by rule
     *
     * @param \Bss\CustomPricing\Model\PriceRule $rule
     * @throws CouldNotSaveException
     */
    protected function saveProductPrice($rule)
    {
        $this->priceRuleSave->saveProductPrice($rule);
    }

    /**
     * Save applied customers by rule
     *
     * @param \Bss\CustomPricing\Model\PriceRule $rule
     * @throws CouldNotSaveException
     */
    protected function saveAppliedCustomers($rule)
    {
        $this->priceRuleSave->saveAppliedCustomers($rule);
    }

    /**
     * Prepare price rule data
     *
     * @param array $data
     *
     * @return array
     */
    protected function prepareData($data)
    {
        if (isset($data['general_information']['rule']['product'])) {
            $data['general_information']['product'] = $data['general_information']['rule']['product'];
        }
        if (isset($data['general_information']['rule']['customer'])) {
            $data['general_information']['customer'] = $data['general_information']['rule']['customer'];
        }
        if (isset($data['general_information']['customer_condition']['is_not_logged_rule'])) {
            $data['general_information']['is_not_logged_rule'] = $data['general_information']
            ['customer_condition']['is_not_logged_rule'];
        }
        if (isset($data['general_information']['configs']['default_price_type'])) {
            $data['general_information']['default_price_type'] = $data['general_information']['configs']['default_price_type'];
        }
        if (isset($data['general_information']['configs']['default_price_value'])) {
            $data['general_information']['default_price_value'] = $data['general_information']['configs']['default_price_value'];
        }

        unset($data['general_information']['rule']);
        unset($data['general_information']['currencies']);
        unset($data['general_information']['configs']);
        $generalInfo = $data['general_information'];
        unset($data["general_information"]);
        return array_merge($data, $generalInfo);
    }
}
