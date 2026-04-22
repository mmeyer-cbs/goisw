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

use Bss\SalesRep\Model\SalesRep;
use Bss\SalesRep\Model\SalesRepRepository;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;

/**
 * Class Additional
 *
 * @package Bss\SalesRep\Block\Adminhtml\Edit\Tab
 */
class Additional extends Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var Config
     */
    protected $_wysiwygConfig;

    /**
     * @var SalesRep
     */
    protected $salesRep;

    /**
     * Additional constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Config $wysiwygConfig
     * @param SalesRepRepository $salesRep
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Config $wysiwygConfig,
        SalesRepRepository $salesRep,
        array $data = []
    ) {
        $this->salesRep = $salesRep;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Add field Additional
     *
     * @return Generic
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $id = $this->getRequest()->getParam('user_id');
        $info = '';
        if ($id) {
            $salesRep = $this->salesRep->getByUserId($id);
            $info = $salesRep->getInformation();
        }
        $this->_coreRegistry->registry('permissions_user');

        $form = $this->_formFactory->create();

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Additional Information')]);

        $fieldset->addField(
            'content',
            'editor',
            [
                'name' => 'content',
                'label' => 'Additional Information',
                'config' => $this->_wysiwygConfig->getConfig(),
                'value' => $info,
                'wysiwyg' => true,
                'required' => false,
            ]
        );

        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * @inheritDoc
     */
    public function getTabLabel()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getTabTitle()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isHidden()
    {
        return false;
    }
}
