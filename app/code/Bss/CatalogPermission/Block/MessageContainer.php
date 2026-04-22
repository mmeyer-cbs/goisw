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
 * @package    Bss_CatalogPermission
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CatalogPermission\Block;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\AbstractProduct;

/**
 * Class MessageContainer
 *
 * @package Bss\CatalogPermission\Block
 */
class MessageContainer extends AbstractProduct
{
    /**
     * To Html
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getTemplate()) {
            $this->setTemplate("Bss_CatalogPermission::message_container.phtml");
        }
        $this->setTemplate($this->getTemplate());
        return parent::_toHtml();
    }
}
