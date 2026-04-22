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
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Api;

/**
 * Class Management
 *
 * @api
 * @since 100.0.0
 */
interface ManagementInterface
{
    /**
     * Get module configs by store id
     *
     * @param int $storeId
     * @return string[]
     */
    public function getConfigByStoreId($storeId = null);

    /**
     * Get module configs
     *
     * @return string[]
     */
    public function getConfig();
}
