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

namespace Bss\CustomPricing\Block\Adminhtml\PriceRule\Edit\Tab\GeneralInformation;

use Bss\CustomPricing\Controller\RegistryConstants;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Abstract class Conditions block
 */
abstract class Conditions extends Generic implements TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $rendererFieldset;

    /**
     * @var \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
     */
    protected $renderConditionsBlock;

    /**
     * @var string
     */
    protected $getConditionsMethod = '';

    /**
     * @var string
     */
    protected $conditionsType = '';

    /**
     * Product conditions constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Data\Form\Element\Renderer\RendererInterface $renderConditionsBlock
     * @param \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset
     * @param string $getConditionsMethod
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Data\Form\Element\Renderer\RendererInterface $renderConditionsBlock,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        string $getConditionsMethod,
        array $data = []
    ) {
        $this->rendererFieldset = $rendererFieldset;
        $this->renderConditionsBlock = $renderConditionsBlock;
        $this->getConditionsMethod = $getConditionsMethod;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Bss\CustomPricing\Model\PriceRule $model */
        $model = $this->_coreRegistry->registry(RegistryConstants::CURRENT_PRICE_RULE);
        try {
            if ($this->getConditionsMethod && $model) {
                $this->setForm(
                    $this->addTabToForm($model, $this->getConditionsMethod)
                );
            }
        } catch (\Exception $e) {
            return $this;
        }

        return parent::_prepareForm();
    }

    /**
     * Add conditions tab to form
     *
     * @param \Bss\CustomPricing\Model\PriceRule $model
     * @param string $conditionsMethod
     *
     * @return \Magento\Framework\Data\Form
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function addTabToForm($model, $conditionsMethod)
    {
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('price_rule_');

        if ($conditions = $model->$conditionsMethod()) {
            $formName = $conditions->getFormName();
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                new \Magento\Framework\Phrase("method not exist!")
            );
        }

        $conditionsFieldSetId = $model
            ->getConditionsFieldSetId($formName . "_" . $this->conditionsType . "_");
        $newChildUrl = $this->getUrl(
            "custom_pricing/priceRules/newConditionHtml/form/" . $conditionsFieldSetId,
            ['form_namespace' => $formName, 'condition_type' => $this->conditionsType]
        );
        $renderer = $this->rendererFieldset
            ->setTemplate('Bss_CustomPricing::price_rule/fieldset.phtml')
            ->setNewChildUrl($newChildUrl)
            ->setFieldSetId($conditionsFieldSetId);

        $fieldset = $form->addFieldset(
            'conditions_fieldset',
            []
        )->setRenderer(
            $renderer
        );

        $fieldset->addField(
            'conditions',
            'text',
            [
                'name' => 'conditions',
                'label' => __('Conditions')
            ]
        )->setRule(
            $model
        )->setRenderer(
            $this->renderConditionsBlock
        );

        $form->setValues($model->getData());
        $this->setConditionFormName($model->getConditions(), $formName, $conditionsFieldSetId);
        return $form;
    }

    /**
     * Load conditions section
     *
     * @param \Magento\Rule\Model\Condition\AbstractCondition $conditions
     * @param string $formName
     * @param string $jsFormName
     * @return void
     */
    private function setConditionFormName(
        \Magento\Rule\Model\Condition\AbstractCondition $conditions,
        $formName,
        $jsFormName
    ) {
        $conditions->setFormName($formName);
        $conditions->setJsFormObject($jsFormName);

        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName, $jsFormName);
            }
        }
    }
}
