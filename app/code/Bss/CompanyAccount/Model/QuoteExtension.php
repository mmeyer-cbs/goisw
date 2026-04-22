<?php
declare(strict_types = 1);

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
 * @package   Bss_CompanyAccount
 * @author    Extension Team
 * @copyright Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class QuoteExtension
 *
 * @package Bss\CompanyAccount\Model
 */
class QuoteExtension extends AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Bss\CompanyAccount\Model\ResourceModel\QuoteExtension');
    }
}
