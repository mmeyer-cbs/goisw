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
namespace Bss\QuoteExtension\Model;

use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\Manager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ModuleCompatible
 */
class ModuleCompatible
{
    const XML_PATH_ENABLED_COMPANY_ACCOUNT = 'bss_company_account/general/enable';
    const PATH_SALES_REP_ENABLED = 'bss_salesrep/general/enable';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var  StoreManagerInterface $storeManagerInterface
     */
    protected $storeManager;

    /**
     * @var Attribute
     */
    protected $eavAttribute;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * ModuleCompatible constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param Manager $moduleManager
     * @param Attribute $eavAttribute
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Manager $moduleManager,
        Attribute $eavAttribute
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->moduleManager =  $moduleManager;
        $this->eavAttribute = $eavAttribute;
    }

    /**
     * Check install module company account
     *
     * @return bool
     */
    public function isInstallCompanyAccount()
    {
        return $this->moduleManager->isEnabled('Bss_CompanyAccount');
    }

    /**
     * Check enable module company account
     *
     * @param null|string $website
     * @return bool
     * @throws LocalizedException
     */
    public function isEnableCompanyAccount($website = null)
    {
        if ($website === null) {
            $website = $this->storeManager->getWebsite()->getId();
        }
        $configEnable = (bool)$this->scopeConfig->getValue(
            self::XML_PATH_ENABLED_COMPANY_ACCOUNT,
            ScopeInterface::SCOPE_WEBSITE,
            $website
        );
        if ($configEnable && $this->isInstallCompanyAccount()) {
            return true;
        }
        return false;
    }

    /**
     * Get attribute id company account
     *
     * @return false|int
     * @throws LocalizedException
     */
    public function getAttributeIdCompanyAccount()
    {
        if ($this->isEnableCompanyAccount()) {
            $attributeId = $this->eavAttribute->getIdByCode("customer", "bss_is_company_account");
            if ($attributeId) {
                return $attributeId;
            }
        }
        return false;
    }

    /**
     * Check install module SalesRep
     *
     * @return bool
     */
    public function isInstallSalesRep()
    {
        return $this->moduleManager->isEnabled('Bss_SalesRep');
    }
}
