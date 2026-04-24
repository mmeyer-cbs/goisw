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
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\QuoteExtension\Plugin\CompanyAccount;

class GetNumberQuoteBySubUser
{
    /**
     * @var \Bss\QuoteExtension\Model\ResourceModel\ManageQuote\CollectionFactory
     */
    protected $manageQuoteFactory;

    /**
     * Construct
     *
     * @param \Bss\QuoteExtension\Model\ResourceModel\ManageQuote\CollectionFactory $manageQuoteFactory
     */
    public function __construct(
        \Bss\QuoteExtension\Model\ResourceModel\ManageQuote\CollectionFactory $manageQuoteFactory
    ) {
        $this->manageQuoteFactory = $manageQuoteFactory;
    }

    /**
     * Get count quote
     *
     * @param \Bss\CompanyAccount\ViewModel\CompatibleQuoteExtension $subject
     * @param int $result
     * @param int $subId
     * @return int
     */
    public function afterGetQuantityOfQuoteBySubUserId($subject, $result, $subId)
    {
        $collection = $this->manageQuoteFactory->create()->addFieldToFilter('main_table.sub_user_id', $subId);
        return count($collection->getData());
    }
}
