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

namespace Bss\CatalogPermission\Api\Data;

/**
 * Interface cms Page to get and set custom attribute
 */
interface CmsPageInterface extends \Magento\Cms\Api\Data\PageInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const BSS_REDIRECT_TYPE = 'bss_redirect_type';
    const BSS_SELECT_PAGE = 'bss_select_page';
    const BSS_CUSTOM_URL = 'bss_custom_url';
    const BSS_ERROR_MESSAGE = 'bss_error_message';
    const BSS_CUSTOMER_GROUP = 'bss_customer_group';
    /**#@-*/

    /**
     * Get redirect type
     * @return string
     */
    public function getBssRedirectType();

    /**
     * Set redirect type
     * @param $redirectType
     * @return string
     */
    public function setBssRedirectType($redirectType);

    /**
     * Get select page
     * @return string
     */
    public function getBssSelectPage();

    /**
     * Set select page
     * @param $selectPage
     * @return string
     */
    public function setBssSelectPage($selectPage);

    /**
     * Get custom url
     * @return string
     */
    public function getBssCustomUrl();

    /**
     * Set custom url
     * @param $customUrl
     * @return string
     */
    public function setBssCustomUrl($customUrl);

    /**
     * Get error message
     * @return string
     */
    public function getBssErrorMessage();

    /**
     * Set error message
     * @param $errorMessage
     * @return string
     */
    public function setBssErrorMessage($errorMessage);

    /**
     * Get customer group
     * @return string
     */
    public function getBssCustomerGroup();

    /**
     * Set customer group
     * @param string $customerGroup
     * @return string[]
     */
    public function setBssCustomerGroup($customerGroup);

}
