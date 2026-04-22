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
 * @category  BSS
 * @package   Bss_ForceLogin
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ForceLogin\Block\Adminhtml\Block\Form\OtherPage\SpecialPage;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\Data\Form\Element\Factory;

class Serialized extends AbstractFieldArray
{
    /**
     * @var Factory
     */
    private $elementFactory;

    /**
     * Serialized constructor.
     *
     * @param Context $context
     * @param Factory $elementFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Factory $elementFactory,
        array $data = []
    ) {
        $this->elementFactory = $elementFactory;
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    public function _construct()
    {
        // create columns
        $this->addColumn('type_url', [
            'label' => __('Type Url'),
            'style' => 'width:300px',
        ]);
        $this->addColumn('url', [
            'label' => __('Url'),
            'style' => 'width:300px'
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');

        parent::_construct();
    }

    /**
     * @param string $columnName
     * @return mixed|string
     * @throws Exception
     */
    public function renderCellTemplate($columnName)
    {
        if ($columnName == 'type_url' && isset($this->_columns[$columnName])) {
            $options = [
                ['value' => 'exactly', 'label' => "Exactly"],
                ['value' => 'contain', 'label' => "Contain"],
            ];

            $element = $this->elementFactory->create('select');
            $element->setForm(
                $this->getForm()
            )->setName(
                $this->_getCellInputElementName($columnName)
            )->setHtmlId(
                $this->_getCellInputElementId('<%- _id %>', $columnName)
            )->setValues(
                $options
            )->setStyle('width:150px');

            return str_replace("\n", '', $element->getElementHtml());
        }

        return parent::renderCellTemplate($columnName);
    }
}
