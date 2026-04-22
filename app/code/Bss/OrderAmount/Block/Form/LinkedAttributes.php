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
 * @package    Bss_OrderAmount
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OrderAmount\Block\Form;

/**
 * Class LinkedAttributes
 *
 * @package Bss\OrderAmount\Block\Form
 */
class LinkedAttributes extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $elementFactory;

    /**
     * @var \Magento\Framework\View\Design\Theme\LabelFactory
     */
    protected $labelFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * LinkedAttributes constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory
     * @param \Magento\Framework\View\Design\Theme\LabelFactory $labelFactory
     * @param \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        \Magento\Framework\View\Design\Theme\LabelFactory $labelFactory,
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->elementFactory = $elementFactory;
        $this->labelFactory = $labelFactory;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * construct
     */
    protected function _construct()
    {
        // create columns
        $this->addColumn('customer_group', [
            'label' => __('Customer Group'),
            'size' => 35,
        ]);
        $this->addColumn('minimum_amount', [
            'label' => __('Minimum Amount'),
            'size' => 16,
            'class' => 'validate-number validate-digits validate-greater-than-zero'
        ]);
        $this->addColumn('message', [
            'label' => __('Message')
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');

        parent::_construct();
    }

    /**
     * @param string $columnName
     * @return mixed|string
     * @throws \Exception
     */
    public function renderCellTemplate($columnName)
    {
        if ($columnName == 'customer_group' && isset($this->_columns[$columnName])) {
            /** @var $label \Magento\Framework\View\Design\Theme\Label */
            $options  = [['value' => '', 'label'=>__('-- Select Group --')]];

            $group = $this->collectionFactory->create();
            foreach ($group as $eachGroup) {
                $option['value'] = $eachGroup->getCustomerGroupId();
                $option['label'] = $eachGroup->getCustomerGroupCode();
                $options[] = $option;
            }

            $element = $this->elementFactory->create('select');
            $element->setForm(
                $this->getForm()
            )->setName(
                $this->_getCellInputElementName($columnName)
            )->setHtmlId(
                $this->_getCellInputElementId('<%- _id %>', $columnName)
            )->setValues(
                $options
            );

            return str_replace("\n", '', $element->getElementHtml());
        }

        return parent::renderCellTemplate($columnName);
    }
}
