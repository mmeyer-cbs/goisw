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
declare(strict_types=1);

namespace Bss\CustomerAttributes\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;

class Relation extends Generic
{

    /**
     * @var \Bss\CustomerAttributes\Model\Config\Source\EnableDisable
     */
    protected $enableDisable;

    /**
     * @var PropertyLocker
     */
    protected $propertyLocker;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param PropertyLocker $propertyLocker
     * @param array $data
     */
    public function __construct(
        Context        $context,
        Registry       $registry,
        FormFactory    $formFactory,
        PropertyLocker $propertyLocker,
        array          $data = []
    ) {
        $this->propertyLocker = $propertyLocker;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Render relation tab
     *
     * @param $layoutBlock
     * @return Relation
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        if ($this->_request->getModuleName() == 'customerattribute') {
            $layoutBlock = $this->getLayout()->createBlock(
                \Bss\CustomerAttributes\Block\Form\Field\DynamicRow::class
            );
        } else {
            $layoutBlock = $this->getLayout()->createBlock(
                \Bss\CustomerAttributes\Block\Form\AddressField\DynamicRow::class
            );
        }
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        $fieldsetRow = $form->addFieldset('discount_update', ['legend' => __('Note: Can be used only with catalog
         input type Yes/No, Dropdown, Radio Button, Checkbox')]);
        $fieldsetRow->addField('dependent_data', 'text', [
            'name' => 'dependent_data',
            'required' => false,
        ])->setRenderer($layoutBlock);
        $this->setForm($form);
        $this->propertyLocker->lock($form);
        return parent::_prepareLayout();
    }
}
