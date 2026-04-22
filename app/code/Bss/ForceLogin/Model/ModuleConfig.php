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
namespace Bss\ForceLogin\Model;

use Bss\ForceLogin\Api\Data\ModuleConfigInterface;
use Magento\Framework\Api\AbstractSimpleObject;

/**
 * ALl backend module configs class
 */
class ModuleConfig extends AbstractSimpleObject implements ModuleConfigInterface
{
    /**
     * @inheritDoc
     */
    public function getEnable()
    {
        return $this->_get(self::ENABLE);
    }

    /**
     * @inheritDoc
     */
    public function setEnable($val)
    {
        return $this->setData(self::ENABLE, $val);
    }

    /**
     * @inheritDoc
     */
    public function getDisableRegistration()
    {
        return $this->_get(self::DISABLE_REGISTRATION);
    }

    /**
     * @inheritDoc
     */
    public function setDisableRegistration($val)
    {
        return $this->setData(self::DISABLE_REGISTRATION, $val);
    }

    /**
     * @inheritDoc
     */
    public function getForceLoginMessage()
    {
        return $this->_get(self::MESSAGE);
    }

    /**
     * @inheritDoc
     */
    public function setForceLoginMessage($val)
    {
        return $this->setData(self::MESSAGE, $val);
    }

    /**
     * @inheritDoc
     */
    public function getForceLoginPageType()
    {
        return $this->_get(self::FORCE_LOGIN_PAGE);
    }

    /**
     * @inheritDoc
     */
    public function setForceLoginPageType($val)
    {
        return $this->setData(self::FORCE_LOGIN_PAGE, $val);
    }

    /**
     * @inheritDoc
     */
    public function getIgnoreRouter()
    {
        return $this->_get(self::IGNORE_ROUTER);
    }

    /**
     * @inheritDoc
     */
    public function setIgnoreRouter($val)
    {
        return $this->setData(self::IGNORE_ROUTER, $val);
    }

    /**
     * @inheritDoc
     */
    public function getListIgnoreRouter()
    {
        return $this->_get(self::LIST_IGNORE_ROUTER);
    }

    /**
     * @inheritDoc
     */
    public function setListIgnoreRouter($val)
    {
        return $this->setData(self::LIST_IGNORE_ROUTER, $val);
    }

    /**
     * @inheritDoc
     */
    public function getForceLoginSpecificPage()
    {
        return $this->_get(self::FORCE_LOGIN_SPECIFIC_PAGE);
    }

    /**
     * @inheritDoc
     */
    public function setForceLoginSpecificPage($val)
    {
        return $this->setData(self::FORCE_LOGIN_SPECIFIC_PAGE, $val);
    }

    /**
     * @inheritDoc
     */
    public function getForceLoginProductPage()
    {
        return $this->_get(self::FORCE_LOGIN_PRODUCT_PAGE);
    }

    /**
     * @inheritDoc
     */
    public function setForceLoginProductPage($val)
    {
        return $this->setData(self::FORCE_LOGIN_PRODUCT_PAGE, $val);
    }

    /**
     * @inheritDoc
     */
    public function getForceLoginCategoryPage()
    {
        return $this->_get(self::FORCE_LOGIN_CATEGORY_PAGE);
    }

    /**
     * @inheritDoc
     */
    public function setForceLoginCategoryPage($val)
    {
        return $this->setData(self::FORCE_LOGIN_CATEGORY_PAGE, $val);
    }

    /**
     * @inheritDoc
     */
    public function getForceLoginCartPage()
    {
        return $this->_get(self::FORCE_LOGIN_CART_PAGE);
    }

    /**
     * @inheritDoc
     */
    public function setForceLoginCartPage($val)
    {
        return $this->setData(self::FORCE_LOGIN_CART_PAGE, $val);
    }

    /**
     * @inheritDoc
     */
    public function getForceLoginCheckoutPage()
    {
        return $this->_get(self::FORCE_LOGIN_CHECKOUT_PAGE);
    }

    /**
     * @inheritDoc
     */
    public function setForceLoginCheckoutPage($val)
    {
        return $this->setData(self::FORCE_LOGIN_CHECKOUT_PAGE, $val);
    }

    /**
     * @inheritDoc
     */
    public function getForceLoginSearchTermPage()
    {
        return $this->_get(self::FORCE_LOGIN_SEARCH_TERM_PAGE);
    }

    /**
     * @inheritDoc
     */
    public function setForceLoginSearchTermPage($val)
    {
        return $this->setData(self::FORCE_LOGIN_SEARCH_TERM_PAGE, $val);
    }

    /**
     * @inheritDoc
     */
    public function getForceLoginAdvancedSearchPage()
    {
        return $this->_get(self::FORCE_LOGIN_ADVANCED_SEARCH_PAGE);
    }

    /**
     * @inheritDoc
     */
    public function setForceLoginAdvancedSearchPage($val)
    {
        return $this->setData(self::FORCE_LOGIN_ADVANCED_SEARCH_PAGE, $val);
    }

    /**
     * @inheritDoc
     */
    public function getForceLoginSearchResultPage()
    {
        return $this->_get(self::FORCE_LOGIN_SEARCH_RESULT_PAGE);
    }

    /**
     * @inheritDoc
     */
    public function setForceLoginSearchResultPage($val)
    {
        return $this->setData(self::FORCE_LOGIN_SEARCH_RESULT_PAGE, $val);
    }

    /**
     * @inheritDoc
     */
    public function getForceLoginContactPage()
    {
        return $this->_get(self::FORCE_LOGIN_CONTACT_PAGE);
    }

    /**
     * @inheritDoc
     */
    public function setForceLoginContactPage($val)
    {
        return $this->setData(self::FORCE_LOGIN_CONTACT_PAGE, $val);
    }

    /**
     * @inheritDoc
     */
    public function getForceLoginCmsPage()
    {
        return $this->_get(self::FORCE_LOGIN_CMS_PAGE);
    }

    /**
     * @inheritDoc
     */
    public function setForceLoginCmsPage($val)
    {
        return $this->setData(self::FORCE_LOGIN_CMS_PAGE, $val);
    }

    /**
     * @inheritDoc
     */
    public function getForceLoginCmsPageIds()
    {
        return $this->_get(self::FORCE_LOGIN_SPECIFIC_CMS_IDS);
    }

    /**
     * @inheritDoc
     */
    public function setForceLoginCmsPageIds($val)
    {
        return $this->setData(self::FORCE_LOGIN_SPECIFIC_CMS_IDS, $val);
    }

    /**
     * @inheritDoc
     */
    public function getRedirectTo()
    {
        return $this->_get(self::AFTER_LOGIN_REDIRECT_TO_PAGE);
    }

    /**
     * @inheritDoc
     */
    public function setRedirectTo($val)
    {
        return $this->setData(self::AFTER_LOGIN_REDIRECT_TO_PAGE, $val);
    }

    /**
     * @inheritDoc
     */
    public function getRedirectCustomUrl()
    {
        return $this->_get(self::AFTER_LOGIN_REDIRECT_TO_CUSTOM_URL);
    }

    /**
     * @inheritDoc
     */
    public function setRedirectCustomUrl($val)
    {
        return $this->setData(self::AFTER_LOGIN_REDIRECT_TO_CUSTOM_URL, $val);
    }

    /**
     * Get all data
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->_data;
    }

    /**
     * Set data
     *
     * @param mixed $key
     * @param mixed $value
     * @return $this|ModuleConfig
     */
    public function setData($key, $value = null)
    {
        if ($key === (array)$key) {
            $this->_data = $key;
        } else {
            $this->_data[$key] = $value;
        }
        return $this;
    }
}
