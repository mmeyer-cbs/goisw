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
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyCredit\Plugin\Customer\Grid;

use Magento\Customer\Model\ResourceModel\Grid\Collection;
use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory;

class JoinCollection
{
    /**
     * JoinLeft table bss_companycredit_credit to table customer_grid
     *
     * @param CollectionFactory $subject
     * @param Collection $collection
     * @param string $requestName
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetReport(
        CollectionFactory $subject,
        $collection,
        $requestName
    ) {
        if ($requestName == 'customer_listing_data_source') {
            $select = $collection->getSelect();
            $select->joinLeft(
                ["bss_companycredit_credit" => $collection->getTable("bss_companycredit_credit")],
                'main_table.entity_id = bss_companycredit_credit.customer_id',
                [
                    "available_credit" => "bss_companycredit_credit.available_credit",
                    "allow_exceed" => "bss_companycredit_credit.allow_exceed",
                    "currency_code" => "bss_companycredit_credit.currency_code"
                ]
            );
        }
        return $collection;
    }
}
