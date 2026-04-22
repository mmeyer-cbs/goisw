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
 * @package    Bss_FastOrder
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\FastOrder\Helper;

use  Magento\Framework\Registry;

/**
 * Class Integrate
 * @package Bss\FastOrder\Helper
 */
class Integrate extends \Magento\Framework\App\Helper\AbstractHelper
{
    //@codingStandardsIgnoreStart
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;

    /**
     * Integrate constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\View\LayoutInterface $layout
     */
    public function __construct(
        Registry $registry,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\View\LayoutInterface $layout
    ) {
        $this->registry = $registry;
        parent::__construct($context);
        $this->objectManager = $objectManager;
        $this->layout = $layout;
    }

    /**
     * Check module configurable grid view install
     *
     * @return bool
     */
    public function isConfigurableGridViewModuleInstall()
    {
        return $this->_moduleManager->isEnabled('Bss_ConfiguableGridView');
    }

    /**
     * Check module configurable grid view enable
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isConfigurableGridViewModuleEnabled()
    {
        if ($this->isConfigurableGridViewModuleInstall()) {
            $helper = $this->objectManager->create(\Bss\ConfiguableGridView\Helper\Data::class);
            if ($helper->isEnabled()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Render block configurable depend module configurable
     *
     * @param boolean $isEditPopup
     * @return \Magento\Framework\View\Element\BlockInterface
     */
    public function getConfigurableGridViewModuleBlock($isEditPopup)
    {
        $block = $this->layout->createBlock('Bss\FastOrder\Block\Product\Renderer\Configurable');
        $disableGridView = $this->registry->registry('current_product')->getDisableGridTableView();
        if ($disableGridView) {
            return $block;
        }
        if ($this->isConfigurableGridViewModuleEnabled() && $isEditPopup != 'true') {
            $block = $this->layout->createBlock('Bss\ConfiguableGridView\Block\Product\View\Configurable');
        }

        return $block;
    }

    /**
     * Check module request for quote install
     *
     * @return bool
     */
    public function isRequestForQuoteModuleInstall()
    {
        return $this->_moduleManager->isEnabled('Bss_QuoteExtension');
    }

    /**
     * Check module request for quote install
     *
     * @return bool
     */
    public function isRequestForQuoteModuleEnabled()
    {
        if ($this->isRequestForQuoteModuleInstall()) {
            $helper = $this->objectManager->create(\Bss\QuoteExtension\Helper\Data::class);
            return $helper->isEnable();
        }

        return false;
    }


    /**
     * @return bool
     */
    public function isRequestForQuoteModuleActive()
    {
        if ($this->isRequestForQuoteModuleEnabled()) {
            $configShowHelper = $this->objectManager->create(\Bss\QuoteExtension\Helper\Admin\ConfigShow::class);
            return $configShowHelper->isEnableOtherPage();
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getRequestForQuoteButtonText()
    {
        $helperShow = $this->objectManager->create(\Bss\QuoteExtension\Helper\Admin\ConfigShow::class);
        return $helperShow->getOtherPageText() ? $helperShow->getOtherPageText() : __('Add to Quote');
    }

    /**
     * @return mixed
     */
    public function getRequestForQuoteButtonStyle()
    {
        $helperShow = $this->objectManager->create(\Bss\QuoteExtension\Helper\Admin\ConfigShow::class);
        return $helperShow->getOtherPageCustomStyle();
    }

    /**
     * @return mixed
     */
    public function getRequestForQuoteModel()
    {
        return $this->objectManager->create(\Bss\QuoteExtension\Model\QuoteExtension::class);
    }

    /**
     * @return mixed
     */
    public function getRequestForQuoteHelper()
    {
        return $this->objectManager->create(\Bss\QuoteExtension\Helper\Data::class);
    }
}
//@codingStandardsIgnoreEnd
