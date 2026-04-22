<?php
declare(strict_types=1);

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
namespace Bss\CompanyAccount\Ui\Component\Listing\SubUser\Column;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class SubStatus
 *
 * @package Bss\CompanyAccount\Ui\Component\Listing\SubUser\Column
 */
class SubStatus extends Column
{
    /**
     * Prepare data source
     *
     * Change sub_status text
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource($dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$items) {
                $items['sub_status'] = $items['sub_status'] == "1" ? __("Enable") : __("Disabled");
            }
        }
        return $dataSource;
    }
}
