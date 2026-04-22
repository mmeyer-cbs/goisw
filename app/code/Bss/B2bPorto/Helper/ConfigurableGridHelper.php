<?php
/**
 *  BSS Commerce Co.
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the EULA
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category    BSS
 * @package     BSS_B2bPorto
 * @author      Extension Team
 * @copyright   Copyright © 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license     http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\B2bPorto\Helper;

/**
 * Class ConfigurableGridHelper
 *
 * @package Bss\B2bPorto\Helper
 */
class ConfigurableGridHelper extends Data
{
    /**
     * Return template if module configurable grid view is enabled
     *
     * @param string $templateName
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function templateUseInUpdateQuote($templateName)
    {
        if ($this->isConfigurableGridModEnabled()) {
            return $templateName;
        }
        return 'Bss_QuoteExtension::quoteextension/item/configure/updatecart.phtml';
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isConfigurableGridViewEnabled()
    {
        return $this->isConfigurableGridModEnabled();
    }
}
