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
 * @package    Bss_CompanyCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyCredit\Block\Adminhtml\Edit\Tab;

use Bss\CompanyCredit\Api\CreditRepositoryInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Ui\Component\Layout\Tabs\TabInterface;

class CompanyCredit extends Generic implements TabInterface
{
    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var CreditRepositoryInterface
     */
    protected $companyCreditRepository;

    /**
     * @var Yesno
     */
    private $yesNo;

    /**
     * CompanyCredit constructor.
     *
     * @param AuthorizationInterface $authorization
     * @param CreditRepositoryInterface $companyCreditRepository
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Yesno $yesNo
     * @param array $data
     */
    public function __construct(
        AuthorizationInterface $authorization,
        CreditRepositoryInterface $companyCreditRepository,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Yesno $yesNo,
        array $data = []
    ) {
        $this->authorization = $authorization;
        $this->companyCreditRepository = $companyCreditRepository;
        $this->yesNo = $yesNo;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    public function _prepareForm()
    {
        /** @var Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('bss_companycredit_');

        $form->addFieldset(
            'companycredit_infor_fieldset',
            ['legend' => __('Credit Limit Information')]
        )->setRenderer(
            $this->_layout
                ->createBlock(Fieldset::class)
                ->setTemplate('Bss_CompanyCredit::tab/companycredit/infor.phtml')
        );

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Credit Limit Configuration')]);
        $this->addField($fieldset);
        $form->addFieldset(
            'companycredit_history_fieldset',
            ['legend' => __('Log Transaction')]
        )->setRenderer(
            $this->_layout
                ->createBlock(Fieldset::class)
                ->setTemplate('Bss_CompanyCredit::tab/companycredit/history.phtml')
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Get customer id
     *
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Get tab label
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Credit Information');
    }

    /**
     * Get tab title
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Company Credit');
    }

    /**
     * Can show tab
     *
     * @return bool
     */
    public function canShowTab()
    {
        $isAllowed = $this->authorization->isAllowed("Bss_CompanyCredit::viewCompanyCredit");
        if ($this->getCustomerId() && $isAllowed) {
            return true;
        }
        return false;
    }

    /**
     * Hidden tab is not get customer id
     *
     * @return bool
     */
    public function isHidden()
    {
        if ($this->getCustomerId()) {
            return false;
        }
        return true;
    }

    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * Add field to form
     *
     * @param mixed $fieldset
     * @return $this
     */
    private function addField($fieldset)
    {
        $dataCompanyCredit = $this->getDataCompanyCredit();
        $disabled = 0;
        if (!$dataCompanyCredit["credit_limit"]) {
            $disabled = 1;
        }
        $fieldset->addField(
            'credit_limit',
            'text',
            [
                'name' => 'bss_companycredit[credit_limit]',
                'label' => __('Credit Limit'),
                'title' => __('Credit Limit'),
                'class' => 'validate-zero-or-greater',
                'value' => $dataCompanyCredit["credit_limit"],
                'data-form-part' => $this->getData('target_form'),
                'note' => __("Warning: Only make change when really needed.
                You must assign the customer a credit limit before making changes to the below fields.")
            ]
        );
        $fieldset->addField(
            'update_available',
            'text',
            [
                'name' => 'bss_companycredit[update_available]',
                'label' => __('Update Available Credit'),
                'title' => __('Update Available Credit'),
                'data-form-part' => $this->getData('target_form'),
                'class' => 'validate-number',
                'note' => __('Add prefix "-" to subtract. '),
                "disabled" => $disabled
            ]
        );

        $fieldset->addField(
            'comment',
            'textarea',
            [
                'name' => 'bss_companycredit[comment]',
                'label' => __('Comment'),
                'title' => __('Comment'),
                'data-form-part' => $this->getData('target_form')
            ]
        );

        $fieldset->addField(
            'allow_exceed',
            'select',
            [
                'name' => 'bss_companycredit[allow_exceed]',
                'label' => __('Allow Available Credit Excess'),
                'title' => __('Allow Available Credit Excess'),
                'data-form-part' => $this->getData('target_form'),
                'values' => $this->yesNo->toOptionArray(),
                'value' => $dataCompanyCredit["allow_exceed"]
            ]
        );

        $fieldset->addField(
            'payment_due_date',
            'text',
            [
                'name' => 'bss_companycredit[payment_due_date]',
                'label' => __('Payment Due Date'),
                'title' => __('Payment Due Date'),
                'value' => $dataCompanyCredit['payment_due_date'],
                'data-form-part' => $this->getData('target_form'),
                'class' => 'validate-number validate-greater-than-zero bss-validate-integer'
            ]
        );

        return $this;
    }

    /**
     * Get data company credit.
     *
     * @return array
     */
    public function getDataCompanyCredit()
    {
        $customerId = $this->getRequest()->getParam('id');
        $credit = $this->companyCreditRepository->get($customerId);
        if ($credit) {
            return [
                "credit_limit" => $credit->getCreditLimit(),
                "allow_exceed" => $credit->getAllowExceed(),
                "payment_due_date" => $credit->getPaymentDueDate()
            ];
        }
        return [
            "credit_limit" => "",
            "allow_exceed" => "",
            "payment_due_date" => ""
        ];
    }
}
