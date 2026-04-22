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
 * @package    Bss_CatalogPermission
 * @author     Extension Team
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CatalogPermission\Api;

/**
 * CMS page save interface.
 */
interface CmsPageRepositoryInterface
{
    /**
     * Save csmPage with csm permission using REST API
     *
     * @param \Bss\CatalogPermission\Api\Data\CmsPageInterface $page
     * @return \Bss\CatalogPermission\Api\Data\CmsPageInterface
     */
    public function save($page);
}
