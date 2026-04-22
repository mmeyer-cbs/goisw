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
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Helper;

use Bss\QuoteExtension\Model\ResourceModel\Product\GetConfigButton;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;

/**
 * Class ConfigRequestButton
 *
 * @package Bss\QuoteExtension\Helper
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class ConfigRequestButton extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * get ids categoryEnable
     */
    protected $categoryIdEnable;

    /**
     * @var GetConfigButton
     */
    protected $resourceConfig;

    /**
     * @var Attribute
     */
    protected $eavAttribute;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Customer\Model\Session $customerSession
     */
    protected $customerSession;

    /**
     * ConfigRequestButton constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param GetConfigButton $resourceConfig
     * @param Attribute $eavAttribute
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        GetConfigButton $resourceConfig,
        Attribute $eavAttribute,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct($context);
        $this->resourceConfig = $resourceConfig;
        $this->eavAttribute = $eavAttribute;
        $this->httpContext = $httpContext;
        $this->customerSession = $customerSession;
    }

    /**
     * Get Config Button Enable For Category
     *
     * @param array $categories
     * @param int $storeId
     * @param int $customerGroup
     * @return string
     * @throws \Zend_Db_Statement_Exception
     */
    public function getConfigButtonCategory($categories, $storeId, $customerGroup)
    {
        if (empty($categories)) {
            return 'use-global';
        }
        $enableForCategory = [];
        $hasEnableCategory = $hasUseGlobalCategory = false;
        $requestConfigCategoryId = $this->eavAttribute->getIdByCode('catalog_category', 'quote_category');
        foreach ($categories as $categoryId) {
            $getConfigCategory = $this->resourceConfig
                ->getEnableButtonCategory($storeId, $requestConfigCategoryId, $categoryId);
            if ($getConfigCategory === 1) {
                $enableForCategory[] = $categoryId;
                $hasEnableCategory = true;
            } elseif (!$getConfigCategory) {
                $hasUseGlobalCategory = true;
            }
        }

        if (!empty($enableForCategory)) {
            return $this->getConfigCustomerGroup($enableForCategory, $customerGroup, $storeId);
        }

        if (!$hasEnableCategory) {
            return $hasUseGlobalCategory ? 'use-global' : 'disable';
        }
        return 'use-global';
    }

    /**
     * Get Config Button Customer Group For Category
     *
     * @param array $categories
     * @param int $customerGroup
     * @param int $storeId
     * @return string
     * @throws \Zend_Db_Statement_Exception
     */
    public function getConfigCustomerGroup($categories, $customerGroup, $storeId)
    {
        $customerGroupSelecteds = [];
        $categoryCustomerGroupId = $this->eavAttribute->getIdByCode('catalog_category', 'quote_category_cus_group');
        foreach ($categories as $categoryId) {
            $customerGroupSelecteds = array_merge(
                $customerGroupSelecteds,
                $this->resourceConfig
                ->getEnableButtonCustomerGroup(
                    $storeId,
                    $categoryCustomerGroupId,
                    $categoryId
                )
            );
        }
        if (in_array($customerGroup, $customerGroupSelecteds)) {
            return 'enable';
        }
        return 'disable';
    }

    /**
     * Get Customer Group Id
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        $isLoggedIn = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        if ($isLoggedIn) {
            return $this->customerSession->getCustomer()->getGroupId();
        }
        return 0;
    }
}
