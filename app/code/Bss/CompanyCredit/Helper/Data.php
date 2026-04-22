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
 * @package    Bss_CompanyCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyCredit\Helper;

use Bss\CompanyCredit\Model\History;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    const XML_PATH_ENABLE_MODULE = 'companycredit/general/active';
    const XML_PATH_PAYMENT_PURCHASEORDER_ACTIVE = "payment/purchaseorder/active";

    /**
     * @var PriceCurrencyInterface $priceCurrency
     */
    protected $priceCurrency;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    private $currency;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * Construct class HelperData
     *
     * @param PriceCurrencyInterface $priceCurrency
     * @param StoreManagerInterface $storeManager
     * @param CurrencyFactory $currency
     * @param Context $context
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        StoreManagerInterface $storeManager,
        CurrencyFactory $currency,
        Context $context
    ) {
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->currency = $currency;
    }

    /**
     * Check module is enable with website scope
     *
     * @param null|int $website
     * @return bool
     */
    public function isEnableModule($website = null)
    {
        try {
            if ($website === null) {
                $website = $this->storeManager->getWebsite()->getId();
            }
            $configModule = $this->scopeConfig->getValue(
                self::XML_PATH_ENABLE_MODULE,
                ScopeInterface::SCOPE_WEBSITE,
                $website
            );
            $configPaymentPurchaseOrder = $this->scopeConfig->getValue(
                self::XML_PATH_PAYMENT_PURCHASEORDER_ACTIVE,
                ScopeInterface::SCOPE_WEBSITE,
                $website
            );
            return $configModule && $configPaymentPurchaseOrder;
        } catch (\Exception $exception) {
            $this->logError($exception->getMessage());
            return 0;
        }
    }

    /**
     * Get Format Price
     *
     * @param float $price
     * @return string
     */
    public function getFormatedPrice($price)
    {
        return $this->priceCurrency->convertAndFormat($price);
    }

    /**
     * Get action company credit
     *
     * @param int $value
     * @param mixed $allowExceed
     * @return string
     */
    public function getTypeAction($value, $allowExceed)
    {
        $result = '';
        $allowExceed = $this->typeExcess($allowExceed);
        switch ($value) {
            case History::TYPE_PLACE_ORDER:
                $result = __("Place order");
                break;
            case History::TYPE_ADMIN_REFUND:
                $result = __("Update Available Credit");
                break;
            case History::TYPE_ADMIN_CHANGES_CREDIT_LIMIT:
                $result = __("Change Credit Limit");
                break;
            case History::TYPE_CHANGE_CREDIT_EXCESS_TO:
                $result = $allowExceed;
                break;
            default:
                break;
        }
        return $result;
    }

    /**
     * Convert Yes or No
     *
     * @param string $value
     * @return \Magento\Framework\Phrase
     */
    public function convertYesNo($value)
    {
        if ($value) {
            return __("Yes");
        }
        return __("No");
    }

    /**
     * Check enable or disable credit excees
     *
     * @param string $value
     * @return \Magento\Framework\Phrase
     */
    public function typeExcess($value)
    {
        if ($value) {
            return __("Enable Credit Excess");
        }
        return __("Disable Credit Excess");
    }

    /**
     * Log error message
     *
     * @param string $message
     */
    public function logError($message)
    {
        $this->_logger->critical($message);
    }
}
