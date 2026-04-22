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
 * @package    Bss_CompanyCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Model\ResourceModel\QEOld;

use Bss\QuoteExtension\Model\QEOld;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 */
class Collection extends AbstractCollection
{
    protected $_idFieldName = "id";
    protected $_eventPrefix = 'bss_qe_collection';
    protected $_eventObject = 'qe_collection';

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(QEOld::class, \Bss\QuoteExtension\Model\ResourceModel\QEOld::class);
    }
}
