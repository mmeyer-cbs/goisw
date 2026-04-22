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
 * @package    Bss_SalesRep
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SalesRep\Block\Adminhtml\Edit\Tab;

use Bss\SalesRep\Helper\Data;
use Bss\SalesRep\Model\Entity\Attribute\Source\SalesRepresentive;
use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Customer\Model\Customer;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;

/**
 * Class SalesRepresentative
 *
 * @package Bss\SalesRep\Block\Adminhtml\Edit\Tab
 */
class SalesRepresentative extends Generic implements TabInterface
{

    /**
     * @var SalesRepresentive
     */
    protected $salesRepresentive;

    /**
     * @var Customer
     */
    protected $customerFactory;

    /**
     * @var Config
     */
    protected $wysiwygConfig;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var FilterProvider
     */
    protected $filterProvider;

    /**
     * SalesRepresentative constructor.
     * @param FilterProvider $filterProvider
     * @param Data $helper
     * @param Customer $customerFactory
     * @param FormFactory $formFactory
     * @param Context $context
     * @param Registry $registry
     * @param SalesRepresentive $salesRepresentive
     * @param Config $wysiwygConfig
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        FilterProvider $filterProvider,
        \Bss\SalesRep\Helper\Data $helper,
        Customer $customerFactory,
        FormFactory $formFactory,
        Context $context,
        Registry $registry,
        SalesRepresentive $salesRepresentive,
        Config $wysiwygConfig,
        array $data = []
    ) {
        $this->filterProvider = $filterProvider;
        $this->helper = $helper;
        $this->salesRepresentive = $salesRepresentive;
        $this->customerFactory = $customerFactory;
        $this->wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    public function getTabLabel()
    {
        return 'Sales Rep';
    }

    /**
     * @inheritdoc
     */
    public function getTabTitle()
    {
        return 'Sales Rep';
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        if ($this->helper->isEnable() && !$this->helper->checkUserIsSalesRep()) {
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
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
     * Add Tab Sales Representative
     *
     * @return Generic
     * @throws LocalizedException
     */
    public function _prepareForm()
    {
        if ($this->helper->isEnable()) {
            $form = $this->_formFactory->create();

            $fieldset = $form->addFieldset(
                'sales_representative_fieldset',
                ['legend' => __('Sales Rep')]
            );
            $this->addField($fieldset);

            $this->setForm($form);
        }
        return parent::_prepareForm();
    }

    /**
     * Field Information
     *
     * @return string
     * @throws Exception
     */
    protected function addFieldInformation()
    {
        $id = $this->getRequest()->getParam('id');
        $customer = $this->customerFactory->load($id)->getData();
        $salesRep = $this->helper->getSalesRep($id)->getData();
        $information = '';
        $userId = null;
        if ($salesRep != null && $customer != null) {
            if ($salesRep['information'] != null) {
                $information = $salesRep['information'];
            }
            $userId = $salesRep['user_id'];
        }
        $informations = $this->filterProvider->getBlockFilter()->filter($information);
        $link = sprintf("location.href = '%s';", $this->getCustomUrl($userId));
        $html = '';
        $html.= '<div class="admin__field field field-content ">';
        $html.= '<label class="label admin__field-label"><span>Additional Information</span></label>';
        $html.= '<div class="bss_sales_rep_container">';
        if ($information != '') {
            $html .= '<div class="bss_sales_rep_information">' . $informations . '</div>';
        }
        $html.= '<input class="bss_sales_rep_edit" type="button" name="bss_sales_rep_edit" value="Edit" onclick="'
                . $link . '"/>';
        $html.= '</div>';
        $html.= '</div>';
        return $html;
    }

    /**
     * Get form HTML
     *
     * @return string
     * @throws Exception
     */
    public function getFormHtml()
    {
        if (is_object($this->getForm())) {
            $html = $this->getForm()->getHtml();
            $html.= $this->addFieldInformation();

            return $html;
        }
        return '';
    }

    /**
     * Add Fields
     *
     * @param string $fieldset
     * @return $this
     * @throws Exception
     */
    private function addField($fieldset)
    {
        $id = $this->getRequest()->getParam('id');
        $customer = $this->customerFactory->load($id)->getData();
        $salesRep = $this->helper->getSalesRep($id)->getData();
        $salesRepCus = null;

        if ($salesRep != null && $customer != null) {
            $salesRepCus = $customer['bss_sales_representative'];
        }
        $fieldset->addField(
            'sales_rep_select',
            'select',
            [
                'name' => 'bss_sales_rep',
                'label' => __('Sales Rep'),
                'title' => __('Sales Rep'),
                'value' => $salesRepCus,
                'values' => $this->salesRepresentive->toOptionArray(),
                'data-form-part' => $this->getData('target_form'),
                'note' => __('Select Sales Rep')
            ]
        );

        return $this;
    }

    /**
     * Get Url
     *
     * @param int $id
     * @return string
     */
    public function getCustomUrl($id)
    {
        return $this->getUrl('adminhtml/user/edit', ['user_id' => $id, '_current' => false]);
    }
}
