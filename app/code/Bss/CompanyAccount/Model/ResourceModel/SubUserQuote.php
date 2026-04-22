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
namespace Bss\CompanyAccount\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class SubUserQuote extends AbstractDb
{
    const TABLE = 'bss_sub_quote';
    const ID = 'entity_id';

    /**
     * Function construct
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(self::TABLE, self::ID);
    }

    /**
     * Load data by quote id
     *
     * @param int $quoteId
     * @param \Bss\CompanyAccount\Model\SubUserQuote $subUserQuote
     * @return SubUserQuote
     */
    public function loadByQuoteId(int $quoteId, \Bss\CompanyAccount\Model\SubUserQuote $subUserQuote): SubUserQuote
    {
        $connection = $this->getConnection();
        $select = $this->_getLoadSelect('quote_id', $quoteId, $subUserQuote);
        $data = $connection->fetchRow($select);

        if ($data) {
            $subUserQuote->setData($data);
        }

        $this->_afterLoad($subUserQuote);

        return $this;
    }

}
