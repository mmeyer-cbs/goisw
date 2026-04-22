<?php
/**
 * Bss Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   Bss
 * @package    Bss_CatalogPermission
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 Bss Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CatalogPermission\Plugin\Theme\Block\Html;

use Bss\CatalogPermission\Helper\ModuleConfig;
use Magento\Customer\Model\Session\Storage as CustomerSessionStorage;

/**
 * Plugin for \Magento\Theme\Block\Html\Topmenu
 */
class Topmenu
{
    /**
     * @var ModuleConfig
     */
    private $catalogPermissionsConfig;

    /**
     * @var CustomerSessionStorage
     */
    private $customerSessionStorage;

    /**
     * @param ModuleConfig $catalogPermissionsConfig
     * @param CustomerSessionStorage $customerSessionStorage
     */
    public function __construct(
        ModuleConfig $catalogPermissionsConfig,
        CustomerSessionStorage $customerSessionStorage
    ) {
        $this->catalogPermissionsConfig = $catalogPermissionsConfig;
        $this->customerSessionStorage = $customerSessionStorage;
    }

    /**
     * Add Customer Group identifier to cache key.
     *
     * If Catalog Permissions are enabled, we must append a Customer Group ID to the cache key so that menu block
     * caches are not shared between Customer Groups.
     *
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @param array $result
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCacheKeyInfo(\Magento\Theme\Block\Html\Topmenu $subject, $result)
    {
        if ($this->catalogPermissionsConfig->enableCatalogPermission()) {
            $result['customer_group_id'] = $this->customerSessionStorage->getCustomerGroupId();
        }

        return $result;
    }
}
