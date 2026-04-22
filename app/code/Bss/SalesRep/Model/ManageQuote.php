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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SalesRep\Model;

/**
 * Class ManageQuote
 *
 * @package Bss\QuoteExtension\Model
 */
class ManageQuote extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Model event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'quote_extension';

    /**
     * { @inheritDoc }
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Bss\SalesRep\Model\ResourceModel\ManageQuote::class);
    }
}
