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
 * @category   BSS
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Model\ResourceModel\SubUserQuote;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \Bss\CompanyAccount\Model\SubUserQuote::class,
            \Bss\CompanyAccount\Model\ResourceModel\SubUserQuote::class
        );
    }

    /**
     * Merge with table quote
     *
     * @return Collection|void
     */
    protected function _initSelect()
    {
        $this->addFilterToMap('entity_id', 'main_table.entity_id');
        parent::_initSelect();

        $this->getSelect()->joinLeft(
            ['quote' => $this->getTable('quote')],
            'main_table.quote_id = quote.entity_id',
            [
                'customer_id' => 'quote.customer_id',
                'created_at' => 'quote.created_at',
                'grand_total' => 'quote.grand_total',
                'base_grand_total' => 'quote.base_grand_total',
                'subtotal' => 'quote.subtotal',
                'base_subtotal' => 'quote.base_subtotal',
                'base_subtotal_with_discount' => 'quote.base_subtotal_with_discount'
            ]
        );
    }
}
