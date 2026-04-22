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

namespace Bss\CatalogPermission\Model\Api;

/**
 * Method set get data in cms page
 */
class CmsPage extends \Magento\Cms\Model\Page implements \Bss\CatalogPermission\Api\Data\CmsPageInterface
{

    /**
     * Get redirect type
     * @return string
     */
    public function getBssRedirectType()
    {
        return $this->getData(self::BSS_REDIRECT_TYPE);
    }

    /**
     * Set redirect type
     * @param $redirectType
     * @return \Bss\CatalogPermission\Api\Data\CmsPageInterface|string
     */
    public function setBssRedirectType($redirectType)
    {
        return $this->setData(self::BSS_REDIRECT_TYPE, $redirectType);
    }

    /**
     * Get select page
     * @return string|void
     */
    public function getBssSelectPage()
    {
        $this->getData(self::BSS_SELECT_PAGE);
    }

    /**
     * Set select page
     * @param $selectPage
     * @return \Bss\CatalogPermission\Api\Data\CmsPageInterface|string
     */
    public function setBssSelectPage($selectPage)
    {
        return $this->setData(self::BSS_SELECT_PAGE, $selectPage);
    }

    /**
     * Get custom url
     * @return string|void
     */
    public function getBssCustomUrl()
    {
        $this->getData(self::BSS_CUSTOM_URL);
    }

    /**
     * Set custom url
     * @param $customUrl
     * @return \Bss\CatalogPermission\Api\Data\CmsPageInterface|string
     */
    public function setBssCustomUrl($customUrl)
    {
        return $this->setData(self::BSS_CUSTOM_URL, $customUrl);
    }

    /**
     * Get error message
     * @return string|void
     */
    public function getBssErrorMessage()
    {
        $this->getData(self::BSS_ERROR_MESSAGE);
    }

    /**
     * Set error message
     * @param $errorMessage
     * @return \Bss\CatalogPermission\Api\Data\CmsPageInterface|string
     */
    public function setBssErrorMessage($errorMessage)
    {
        return $this->setData(self::BSS_ERROR_MESSAGE, $errorMessage);
    }

    /**
     * Get customer group
     * @return string|void
     */
    public function getBssCustomerGroup()
    {
        $this->getData(self::BSS_CUSTOMER_GROUP);
    }

    /**
     * Set customer group
     * @param $customerGroup
     * @return \Bss\CatalogPermission\Api\Data\CmsPageInterface|string[]
     */
    public function setBssCustomerGroup($customerGroup)
    {
        return $this->setData(self::BSS_CUSTOMER_GROUP, $customerGroup);
    }

    /**
     * Get id
     * @return int|null
     */
    public function getId()
    {
        return parent::getId();
    }

    /**
     * Get identifier
     * @return array|string|string[]
     */
    public function getIdentifier()
    {
        return parent::getIdentities();
    }

    /**
     * Get title
     * @return string|null
     */
    public function getTitle()
    {
        return parent::getTitle();
    }

    /**
     * get page layout
     * @return string|null
     */
    public function getPageLayout()
    {
        return parent::getPageLayout();
    }

    /**
     * Get meta title
     * @return string|null
     */
    public function getMetaTitle()
    {
        return parent::getMetaTitle();
    }

    /**
     * Get meta keywords
     * @return string|null
     */
    public function getMetaKeywords()
    {
        return parent::getMetaKeywords();
    }

    /**
     * get meta description
     * @return string|null
     */
    public function getMetaDescription()
    {
        return parent::getMetaDescription();
    }

    /**
     * get content heading
     * @return string|null
     */
    public function getContentHeading()
    {
        return parent::getContentHeading();
    }

    /**
     * get content
     * @return string|null
     */
    public function getContent()
    {
        return parent::getContent();
    }

    /**
     * get creation time
     * @return string|null
     */
    public function getCreationTime()
    {
        return parent::getCreationTime();
    }

    /**
     * get update time
     * @return string|null
     */
    public function getUpdateTime()
    {
        return parent::getUpdateTime();
    }

    /**
     * get sort order
     * @return string|null
     */
    public function getSortOrder()
    {
        return parent::getSortOrder();
    }

    /**
     * get layout update xml
     * @return string|null
     */
    public function getLayoutUpdateXml()
    {
        return parent::getLayoutUpdateXml();
    }

    /**
     * get custom theme
     * @return string|null
     */
    public function getCustomTheme()
    {
        return parent::getCustomTheme();
    }

    /**
     * get custom root template
     * @return string|null
     */
    public function getCustomRootTemplate()
    {
        return parent::getCustomRootTemplate();
    }

    /**
     * get custom layout update xml
     * @return string|null
     */
    public function getCustomLayoutUpdateXml()
    {
        return parent::getCustomLayoutUpdateXml();
    }

    /**
     * get custom theme from
     * @return string|null
     */
    public function getCustomThemeFrom()
    {
        return parent::getCustomThemeFrom();
    }

    /**
     * get custom theme to
     * @return string|null
     */
    public function getCustomThemeTo()
    {
        return parent::getCustomThemeTo();
    }

    /**
     * get enable or disable page cms
     * @return bool|null
     */
    public function isActive()
    {
        return parent::isActive();
    }

    /**
     * set id
     * @param $id
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setId($id)
    {
        return parent::setId($id);
    }

    /**
     * set Identifier
     * @param $identifier
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setIdentifier($identifier)
    {
        return parent::setIdentifier($identifier);
    }

    /**
     * set title
     * @param $title
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setTitle($title)
    {
        return parent::setTitle($title);
    }

    /**
     * set page layout
     * @param $pageLayout
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setPageLayout($pageLayout)
    {
        return parent::setPageLayout($pageLayout);
    }

    /**
     * set meta title
     * @param $metaTitle
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setMetaTitle($metaTitle)
    {
        return parent::setMetaTitle($metaTitle);
    }

    /**
     * set meta keywords
     * @param $metaKeywords
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setMetaKeywords($metaKeywords)
    {
        return parent::setMetaKeywords($metaKeywords);
    }

    /**
     * set meta description
     * @param $metaDescription
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setMetaDescription($metaDescription)
    {
        return parent::setMetaDescription($metaDescription);
    }

    /**
     * set content heading
     * @param $contentHeading
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setContentHeading($contentHeading)
    {
        return parent::setContentHeading($contentHeading);
    }

    /**
     * set content
     * @param $content
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setContent($content)
    {
        return parent::setContent($content);
    }

    /**
     * set creation time
     * @param $creationTime
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setCreationTime($creationTime)
    {
        return parent::setCreationTime($creationTime);
    }

    /**
     * set update time
     * @param $updateTime
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setUpdateTime($updateTime)
    {
        return parent::setUpdateTime($updateTime);
    }

    /**
     * set sort order
     * @param $sortOrder
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setSortOrder($sortOrder)
    {
        return parent::setSortOrder($sortOrder);
    }

    /**
     * set layout update xml
     * @param $layoutUpdateXml
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setLayoutUpdateXml($layoutUpdateXml)
    {
        return parent::setLayoutUpdateXml($layoutUpdateXml);
    }

    /**
     * set custom theme
     * @param $customTheme
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setCustomTheme($customTheme)
    {
        return parent::setCustomTheme($customTheme);
    }

    /**
     * set custom root template
     * @param $customRootTemplate
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setCustomRootTemplate($customRootTemplate)
    {
        return parent::setCustomRootTemplate($customRootTemplate);
    }

    /**
     * set custom layout update xml
     * @param $customLayoutUpdateXml
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setCustomLayoutUpdateXml($customLayoutUpdateXml)
    {
        return parent::setCustomLayoutUpdateXml($customLayoutUpdateXml);
    }

    /**
     * set custom theme from
     * @param $customThemeFrom
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setCustomThemeFrom($customThemeFrom)
    {
        return parent::setCustomThemeFrom($customThemeFrom);
    }

    /**
     * set custom theme to
     * @param $customThemeTo
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setCustomThemeTo($customThemeTo)
    {
        return parent::setCustomThemeTo($customThemeTo);
    }

    /**
     * set is active
     * @param $isActive
     * @return \Magento\Cms\Api\Data\PageInterface
     */
    public function setIsActive($isActive)
    {
        return parent::setIsActive($isActive);
    }
}
