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
namespace Bss\CustomPricing\Controller\Adminhtml\Applied;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Bss\CustomPricing\Api\AppliedCustomersRepositoryInterface;

/**
 * Class InlineEdit
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class InlineEdit extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Bss_CustomPricing::custom_pricing_edit_rule';

    /**
     * @var AppliedCustomersRepositoryInterface
     */
    protected $appliedRepository;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @param Context $context
     * @param AppliedCustomersRepositoryInterface $appliedRepository
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        AppliedCustomersRepositoryInterface $appliedRepository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->appliedRepository = $appliedRepository;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * Inline edit applied customer grid
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $ruleId) {
                    /** @var \Bss\CustomPricing\Model\AppliedCustomers $rule */
                    $rule = $this->appliedRepository->getById($ruleId);
                    try {
                        $rule->setAppliedRule($postItems[$ruleId]['applied_rule']);
                        $this->appliedRepository->save($rule);
                    } catch (\Exception $e) {
                        $messages[] = $this->getErrorWithRuleId(
                            $rule,
                            __($e->getMessage())
                        );
                        $error = true;
                    }
                }
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * @param \Bss\CustomPricing\Model\AppliedCustomers $rule
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithRuleId($rule, $errorText)
    {
        return '[Rule ID: ' . $rule->getId() . '] ' . $errorText;
    }
}
