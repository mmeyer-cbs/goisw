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
namespace Bss\CatalogPermission\Model\Plugin\Theme\Block\Html;

use Bss\CatalogPermission\Helper\ModuleConfig;
use Magento\Customer\Model\Session;
use Magento\Theme\Block\Html\Topmenu as TopmenuBlock;

/**
 * Topmenu plugin.
 */
class Topmenu
{
    /**
     * Current customer session.
     *
     * @var Session
     */
    private $session;

    /**
     * Config with catalog permissions.
     *
     * @var ModuleConfig
     */
    private $permissionsConfig;

    /**
     * @param ModuleConfig $permissionsConfig
     * @param Session $session
     */
    public function __construct(
        ModuleConfig $permissionsConfig,
        Session $session
    ) {
        $this->permissionsConfig = $permissionsConfig;
        $this->session = $session;
    }

    /**
     * Plugin that generates unique block cache key depending on customer group.
     *
     * @param TopmenuBlock $block
     * @return null
     */
    public function beforeToHtml(TopmenuBlock $block)
    {
        if ($this->permissionsConfig->enableCatalogPermission()) {
            $customerGroupId = $this->session->getCustomerGroupId();
            $key = $block->getCacheKeyInfo();
            if ($key) {
                $key = array_values($key);
                $key[] = $customerGroupId;
                $key = implode('|', $key);
                $key = sha1($key);
                $block->setData('cache_key', $key);
            }
        }
        return null;
    }
}
