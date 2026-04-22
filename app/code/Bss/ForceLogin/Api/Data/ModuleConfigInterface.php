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
 * @package    Bss_ForceLogin
 * @author     Extension Team
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ForceLogin\Api\Data;

/**
 * Interface ModuleConfigInterface
 */
interface ModuleConfigInterface
{
    const ENABLE = "enable";
    const DISABLE_REGISTRATION = "disable_register";
    const MESSAGE = "message";
    const FORCE_LOGIN_PAGE = "force_login_page";
    const IGNORE_ROUTER = "ignore_router";
    const LIST_IGNORE_ROUTER = "list_ignore_router";
    const FORCE_LOGIN_SPECIFIC_PAGE = "force_router_special";
    const FORCE_LOGIN_PRODUCT_PAGE = "product_page";
    const FORCE_LOGIN_CATEGORY_PAGE = "category_page";
    const FORCE_LOGIN_CART_PAGE = "cart_page";
    const FORCE_LOGIN_CHECKOUT_PAGE = "checkout_page";
    const FORCE_LOGIN_SEARCH_TERM_PAGE = "search_term_page";
    const FORCE_LOGIN_ADVANCED_SEARCH_PAGE = "advanced_search_page";
    const FORCE_LOGIN_SEARCH_RESULT_PAGE = "search_result_page";
    const FORCE_LOGIN_CONTACT_PAGE = "contact_page";
    const FORCE_LOGIN_CMS_PAGE = "enable_cms_page";
    const FORCE_LOGIN_SPECIFIC_CMS_IDS = "cms_page_ids";
    const AFTER_LOGIN_REDIRECT_TO_PAGE = "redirect_to";
    const AFTER_LOGIN_REDIRECT_TO_CUSTOM_URL = "custom_url";

    /**
     * Get module enable
     *
     * @return int
     */
    public function getEnable();

    /**
     * Set enable module
     *
     * @param int $val
     * @return $this
     */
    public function setEnable($val);

    /**
     * Get disable registration
     *
     * @return int
     */
    public function getDisableRegistration();

    /**
     * Set disable registration
     *
     * @param int $val
     * @return $this
     */
    public function setDisableRegistration($val);

    /**
     * Get force login message
     *
     * @return string
     */
    public function getForceLoginMessage();

    /**
     * Set force login message
     *
     * @param string $val
     * @return $this
     */
    public function setForceLoginMessage($val);

    /**
     * Get force login page type
     *
     * @return int
     */
    public function getForceLoginPageType();

    /**
     * Set force login page type
     *
     * @param int $val
     * @return $this
     */
    public function setForceLoginPageType($val);

    /**
     * Get ignore router
     *
     * @return string[]
     */
    public function getIgnoreRouter();

    /**
     * Set ignore router
     *
     * @param array $val
     * @return $this
     */
    public function setIgnoreRouter($val);

    /**
     * Get list ignore router
     *
     * @return string[]
     */
    public function getListIgnoreRouter();

    /**
     * Set list ignore router
     *
     * @param array $val
     * @return $this
     */
    public function setListIgnoreRouter($val);

    /**
     * Get force login specific page
     *
     * @return string
     */
    public function getForceLoginSpecificPage();

    /**
     * Set force login specific page
     *
     * @param string $val
     * @return $this
     */
    public function setForceLoginSpecificPage($val);

    /**
     * Get force login product page
     *
     * @return int
     */
    public function getForceLoginProductPage();

    /**
     * Get force login product page
     *
     * @param int $val
     * @return $this
     */
    public function setForceLoginProductPage($val);

    /**
     * Get force login category page
     *
     * @return int
     */
    public function getForceLoginCategoryPage();

    /**
     * Set force login category page
     *
     * @param int $val
     * @return $this
     */
    public function setForceLoginCategoryPage($val);

    /**
     * Get force login cart page
     *
     * @return int
     */
    public function getForceLoginCartPage();

    /**
     * Set force login cart page
     *
     * @param string $val
     * @return $this
     */
    public function setForceLoginCartPage($val);

    /**
     * Get force login checkout page
     *
     * @return int
     */
    public function getForceLoginCheckoutPage();

    /**
     * Set force login checkout page
     *
     * @param int $val
     * @return $this
     */
    public function setForceLoginCheckoutPage($val);

    /**
     * Get force login search term page
     *
     * @return int
     */
    public function getForceLoginSearchTermPage();

    /**
     * Set force login search term page
     *
     * @param int $val
     * @return $this
     */
    public function setForceLoginSearchTermPage($val);

    /**
     * Get force login advanced search page
     *
     * @return int
     */
    public function getForceLoginAdvancedSearchPage();

    /**
     * Set force login advanced search page
     *
     * @param int $val
     * @return $this
     */
    public function setForceLoginAdvancedSearchPage($val);

    /**
     * Get force login search result page
     *
     * @return int
     */
    public function getForceLoginSearchResultPage();

    /**
     * Set force login search result page
     *
     * @param int $val
     * @return $this
     */
    public function setForceLoginSearchResultPage($val);

    /**
     * Get force login contact page
     *
     * @return int
     */
    public function getForceLoginContactPage();

    /**
     * Set force login contact page
     *
     * @param int $val
     * @return $this
     */
    public function setForceLoginContactPage($val);

    /**
     * Get force login cms page
     *
     * @return int
     */
    public function getForceLoginCmsPage();

    /**
     * Set force login cms page
     *
     * @param int $val
     * @return $this
     */
    public function setForceLoginCmsPage($val);

    /**
     * Get force login cms page ids
     *
     * @return mixed|array
     */
    public function getForceLoginCmsPageIds();

    /**
     * Get force login cms page ids
     *
     * @param array $val
     * @return $this
     */
    public function setForceLoginCmsPageIds($val);

    /**
     * Get redirect to
     *
     * @return string
     */
    public function getRedirectTo();

    /**
     * Set redirect to
     *
     * @param string $val
     * @return $this
     */
    public function setRedirectTo($val);

    /**
     * Get redirect custom url
     *
     * @return string
     */
    public function getRedirectCustomUrl();

    /**
     * Set redirect custom url
     *
     * @param string $val
     * @return $this
     */
    public function setRedirectCustomUrl($val);
}
