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
 * @package    Bss_StoreCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\StoreCredit\Block\Adminhtml\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Store\Model\StoreRepository;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Customer\Model\Customer\Attribute\Source\Website;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset;
use Magento\Backend\Block\Widget\Form\Element\Dependence;

/**
 * Class StoreCredit
 * @package Bss\StoreCredit\Block\Adminhtml\Edit\Tab
 */
class StoreCredit extends Generic implements TabInterface
{
    /**
     * @var \Magento\Customer\Model\Customer\Attribute\Source\Website
     */
    private $website;

    /**
     * @var Yesno
     */
    private $yesNo;

    /**
     * @var StoreRepository
     */
    private $storeRepository;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Website $website
     * @param Yesno $yesNo
     * @param StoreRepository $storeRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Website $website,
        Yesno $yesNo,
        StoreRepository $storeRepository,
        array $data = []
    ) {
        $this->website = $website;
        $this->yesNo = $yesNo;
        $this->storeRepository = $storeRepository;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('bss_storecredit_');

        $form->addFieldset(
            'storecredit_statistic_fieldset',
            ['legend' =>__('Statistic')]
        )->setRenderer(
            $this->_layout
                ->createBlock(Fieldset::class)
                ->setTemplate('Bss_StoreCredit::tab/storecredit/statistic.phtml')
        );

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Balance')]);
        $this->addField($fieldset);
        $form->addFieldset(
            'storecredit_history_fieldset',
            ['legend' =>__('History')]
        )->setRenderer(
            $this->_layout
                ->createBlock(Fieldset::class)
                ->setTemplate('Bss_StoreCredit::tab/storecredit/history.phtml')
        );

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                Dependence::class
            )->addFieldMap(
                "bss_storecredit_is_notify",
                'notify'
            )->addFieldMap(
                "bss_storecredit_store_id",
                'store'
            )->addFieldDependence(
                'store',
                'notify',
                '1'
            )
        );
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Credit Information');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Store Credit');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        if ($this->getCustomerId()) {
            return true;
        }
        return false;
    }

    /**
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
     * @return $this
     */
    private function addField($fieldset)
    {
        $fieldset->addField(
            'website_id',
            'select',
            [
                'name' => 'bss_storecredit_balance[website_id]',
                'label' => __('Website'),
                'title' => __('Website'),
                'values' => $this->website->toOptionArray(),
                'data-form-part' => $this->getData('target_form'),
                'note' => __('Select Website')
            ]
        );

        $fieldset->addField(
            'amount_value',
            'text',
            [
                'name' => 'bss_storecredit_balance[amount_value]',
                'label' => __('Update Value'),
                'title' => __('Update Value'),
                'data-form-part' => $this->getData('target_form'),
                'class' => 'validate-number',
                'note' => __('Update Amount. Prefix - for down.')
            ]
        );

        $fieldset->addField(
            'comment_content',
            'textarea',
            [
                'name' => 'bss_storecredit_balance[comment_content]',
                'label' => __('Comment'),
                'title' => __('Comment'),
                'data-form-part' => $this->getData('target_form')
            ]
        );

        $fieldset->addField(
            'is_notify',
            'select',
            [
                'name' => 'bss_storecredit_balance[is_notify]',
                'label' => __('Notify Customer'),
                'title' => __('Notify Customer'),
                'data-form-part' => $this->getData('target_form'),
                'values' => $this->yesNo->toOptionArray(),
                'note' => __('Send mail to customer.')
            ]
        );

        $stores = $this->storeRepository->getList();
        $storeList = [];

        foreach ($stores as $store) {
            if ($store->getIsActive() && $store->getStoreId()) {
                $storeList[] = [
                    'value' => $store->getStoreId(),
                    'label' => $store->getName()
                ];
            }
        }
        $fieldset->addField(
            'store_id',
            'select',
            [
                'name' => 'bss_storecredit_balance[store_id]',
                'label' => __('Send from store'),
                'title' => __('Send from store'),
                'values' => $storeList,
                'data-form-part' => $this->getData('target_form'),
                'note' => __('Select Store')
            ]
        );
        return $this;
    }
}
