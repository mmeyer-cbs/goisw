<?php
/**
 *  BSS Commerce Co.
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the EULA
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category    BSS
 * @package     Bss_B2bPorto
 * @author      Extension Team
 * @copyright   Copyright © 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license     http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\B2bPorto\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Customer\Model\SessionFactory as CustomerSession;

/**
 * Class Data
 */
class Data extends AbstractHelper
{
    const MAGEPLAZA_AJAX_LAYER_ENABLE_CONFIG_XML_PATH = 'layered_navigation/general/ajax_enable';
    const MAGEPLAZA_LAYERED_NAV_ENABLE_CONFIG_XML_PATH = 'layered_navigation/general/enable';
    const BSS_CONFIGURABLE_GRID_VIEW_ENABLE_CONFIG_XML_PATH = 'configuablegridview/general/active';
    const BSS_CONFIGURABLE_GRID_VIEW_DISABLED_CUSTOMER_GROUP = 'configuablegridview/general/disabled_customer_group';

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonSerializer;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param CustomerSession $customerSession
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
     * @param StoreManager $storeManager
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        CustomerSession $customerSession,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        StoreManager $storeManager
    ) {
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->jsonSerializer = $jsonSerializer;
        parent::__construct($context);
    }

    /**
     * Get config value by storeId
     *
     * @param string $field
     * @param int|null $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfigValue($field, $storeId = null)
    {
        if (!$storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Is mageplaza ajax enabled
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isMageplazaAjaxEnabled()
    {
        return $this->getConfigValue(self::MAGEPLAZA_AJAX_LAYER_ENABLE_CONFIG_XML_PATH);
    }

    /**
     * Is layerd navigation enabled
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isMageplazaLayeredNavEnabled()
    {
        return $this->getConfigValue(self::MAGEPLAZA_LAYERED_NAV_ENABLE_CONFIG_XML_PATH);
    }

    /**
     * If module configurable grid view is enabled
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isConfigurableGridModEnabled()
    {
        if ($this->isModuleOutputEnabled('Bss_ConfiguableGridView')) {
            if ($this->getConfigValue(self::BSS_CONFIGURABLE_GRID_VIEW_ENABLE_CONFIG_XML_PATH)) {
                $disabledCustomerGroup = $this->getConfigValue(
                    self::BSS_CONFIGURABLE_GRID_VIEW_DISABLED_CUSTOMER_GROUP
                );
                if ($disabledCustomerGroup == '') {
                    return true;
                }
                $disabledCustomerGroup = explode(',', $disabledCustomerGroup);
                if (!in_array($this->customerSession->create()->getCustomerGroupId(), $disabledCustomerGroup)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Is use configurable grid view template
     *
     * @param string $name
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isUseConfigurableGridModTemplate($name)
    {
        if ($this->isConfigurableGridModEnabled()) {
            return $name;
        }
        return 'Magento_Catalog::product/view/addtocart.phtml';
    }

    /**
     * Get Json Serializer
     *
     * @return \Magento\Framework\Serialize\Serializer\Json
     */
    public function getJsonSerializer()
    {
        return $this->jsonSerializer;
    }
}
