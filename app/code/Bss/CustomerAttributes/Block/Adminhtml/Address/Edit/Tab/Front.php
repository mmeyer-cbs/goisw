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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomerAttributes\Block\Adminhtml\Address\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

/**
 * Class Front
 *
 * @package Bss\CustomerAttributes\Block\Adminhtml\Address\Edit\Tab
 */
class Front extends Generic
{
    /**
     * @var Yesno
     */
    protected $yesNo;

    /**
     * @var \Bss\CustomerAttributes\Model\Config\Source\EnableDisable
     */
    protected $enableDisable;

    /**
     * @var PropertyLocker
     */
    protected $propertyLocker;

    /**
     * @var \Bss\CustomerAttributes\Model\Config\Source\ShowAttrSection
     */
    private $attrSection;

    /**
     * @var \Bss\CustomerAttributes\Helper\B2BRegistrationIntegrationHelper
     */
    private $integration;

    /**
     * Front constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Yesno $yesNo
     * @param \Bss\CustomerAttributes\Model\Config\Source\EnableDisable $enableDisable
     * @param \Bss\CustomerAttributes\Model\Config\Source\ShowAttrSection $attrSection
     * @param \Bss\CustomerAttributes\Helper\B2BRegistrationIntegrationHelper $integration
     * @param PropertyLocker $propertyLocker
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Yesno $yesNo,
        \Bss\CustomerAttributes\Model\Config\Source\EnableDisable $enableDisable,
        \Bss\CustomerAttributes\Model\Config\Source\ShowAttrSection $attrSection,
        \Bss\CustomerAttributes\Helper\B2BRegistrationIntegrationHelper $integration,
        PropertyLocker $propertyLocker,
        array $data = []
    ) {
        $this->yesNo = $yesNo;
        $this->propertyLocker = $propertyLocker;
        $this->enableDisable = $enableDisable;
        $this->attrSection = $attrSection;
        $this->integration = $integration;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare Front Tab
     *
     * @return Generic
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $attributeObject = $this->_coreRegistry->registry('entity_attribute');
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        $yesnoSource = $this->yesNo->toOptionArray();
        $enableDisable = $this->enableDisable->toOptionArray();
        $fieldset = $form->addFieldset(
            'front_fieldset',
            ['legend' => __('Display Properties'), 'collapsable' => $this->getRequest()->has('popup')]
        );
        $fieldset->addField(
            'sort_order',
            'text',
            [
                'name' => 'sort_order', 'label' => __('Sort Order'),
                'title' => __('Sort Order'), 'class' => 'validate-digits',
                'note' => __('The order to display attribute on the frontend'),
            ]
        );
        $fieldset->addField(
            'is_visible',
            'select',
            [
                'name' => 'is_visible', 'label' => __('Status'),
                'title' => __('Status'), 'values' => $enableDisable,
                'value' => '1',
            ]
        );
        $usedInForms = $attributeObject->getUsedInForms();
        $showOnAdminCheckout = $this->checkShowAttribute(
            $attributeObject,
            $usedInForms,
            'adminhtml_customer_address'
        );
        $fieldset->addField(
            'adminhtml_customer_address',
            'select',
            [
                'name' => 'adminhtml_customer_address', 'label' => __('Display in Admin Checkout'),
                'title' => __('Display in Admin Checkout'), 'values' => $yesnoSource, 'value' => $showOnAdminCheckout,
            ]
        );

        $showCheckoutFrontEnd = $this->checkShowAttribute($attributeObject, $usedInForms, 'show_checkout_frontend');
        $fieldset->addField(
            'show_checkout_frontend',
            'select',
            [
                'name' => 'show_checkout_frontend', 'label' => __('Display in Checkout Page'),
                'title' => __('Display in Checkout Page'), 'values' => $yesnoSource, 'value' => $showCheckoutFrontEnd,
            ]
        );

        $showInAddressBook = $this->checkShowAttribute($attributeObject, $usedInForms, 'customer_address_edit');
        $fieldset->addField(
            'customer_address_edit',
            'select',
            [
                'name' => 'customer_address_edit', 'label' => __('Display in Address Book'),
                'title' => __('Display in Address Book'), 'values' => $yesnoSource, 'value' => $showInAddressBook,
            ]
        );

        $showInOrderDetail = $this->checkShowAttribute($attributeObject, $usedInForms, 'order_detail');
        $fieldset->addField(
            'order_detail',
            'select',
            [
                'name' => 'order_detail', 'label' => __('Display in Order Detail Page'),
                'title' => __('Display in Order Detail Page (Front)'),
                'values' => $yesnoSource,
                'value' => $showInOrderDetail,
            ]
        );

        $showInAdminOrderDetail = $this->checkShowAttribute($attributeObject, $usedInForms, 'adminhtml_order_detail');
        $fieldset->addField(
            'adminhtml_order_detail',
            'select',
            [
                'name' => 'adminhtml_order_detail', 'label' => __('Display in Order Detail Page (Admin)'),
                'title' => __('Display in Order Detail Page (Admin)'),
                'values' => $yesnoSource,
                'value' => $showInAdminOrderDetail,
            ]
        );

        $fieldset->addField(
            'is_used_in_grid',
            'select',
            [
                'name' => 'is_used_in_grid', 'label' => __('Display in Customer Grid'),
                'title' => __('Display in Customer Grid'), 'values' => $yesnoSource,
                'value' => $attributeObject->getIsUsedInGrid(),
            ]
        );

        $showInOrderEmail = $this->checkShowAttribute($attributeObject, $usedInForms, 'show_in_order_email');
        $fieldset->addField(
            'show_in_order_email',
            'select',
            [
                'name' => 'show_in_order_email', 'label' => __('Add on Order Email'),
                'title' => __('Add on Order Email'), 'values' => $yesnoSource, 'value' => $showInOrderEmail,
            ]
        );

        $showInEmailInvoice = $this->checkShowAttribute($attributeObject, $usedInForms, 'show_in_invoice_email');
        $fieldset->addField(
            'show_in_invoice_email',
            'select',
            [
                'name' => 'show_in_invoice_email', 'label' => __('Add on Invoice Email'),
                'title' => __('Add on Invoice Email'), 'values' => $yesnoSource, 'value' => $showInEmailInvoice,
            ]
        );

        $showInShippingEmail = $this->checkShowAttribute(
            $attributeObject,
            $usedInForms,
            'show_in_shipping_email'
        );
        $fieldset->addField(
            'show_in_shipping_email',
            'select',
            [
                'name' => 'show_in_shipping_email', 'label' => __('Add on Shipping Email'),
                'title' => __('Add on Shipping Email'), 'values' => $yesnoSource, 'value' => $showInShippingEmail,
            ]
        );

        $showInMemoEmail = $this->checkShowAttribute(
            $attributeObject,
            $usedInForms,
            'show_in_credit_memo_email'
        );
        $fieldset->addField(
            'show_in_credit_memo_email',
            'select',
            [
                'name' => 'show_in_credit_memo_email', 'label' => __('Add on Credit Memos Email'),
                'title' => __('Add on Credit Memos Emai'), 'values' => $yesnoSource, 'value' => $showInMemoEmail,
            ]
        );

        $this->setForm($form);
        $this->propertyLocker->lock($form);
        return parent::_prepareForm();
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attributeObject
     * @param [] $usedInForms
     * @param string $attributeCode
     * @return int
     */
    private function checkShowAttribute($attributeObject, $usedInForms, $attributeCode)
    {
        if ($attributeObject->getAttributeId()) {
            if (in_array($attributeCode, $usedInForms)) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return 1;
        }
    }

    /**
     * Initialize form fields values
     *
     * @return $this
     */
    protected function _initFormValues()
    {
        $data = $this->getAttributeObject()->getData();
        if (isset($data['sort_order'])) {
            $data['sort_order'] = $data['sort_order'] - \Bss\CustomerAttributes\Helper\Data::DEFAULT_SORT_ORDER;
        }
        $this->getForm()->addValues($data);
        return parent::_initFormValues();
    }

    /**
     * Retrieve attribute object from registry
     *
     * @return mixed
     */
    private function getAttributeObject()
    {
        return $this->_coreRegistry->registry('entity_attribute');
    }
}
