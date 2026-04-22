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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyAccount\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Store\Model\ScopeInterface;

class CompatibleQuoteExtension
{
    const XML_PATH_ENABLED_QUOTE_EXTENSION = "bss_request4quote/general/enable";

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Bss\CompanyAccount\Model\SubUserFactory
     */
    protected $subUserFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Bss\CompanyAccount\Model\SubUserOrderRepository
     */
    protected $subUserOrderRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Bss\CompanyAccount\Model\ResourceModel\SubUser\CollectionFactory
     */
    protected $subUserCollectionFactory;

    /**
     * Construct
     *
     * @param \Bss\CompanyAccount\Model\ResourceModel\SubUser\CollectionFactory $subUserCollectionFactory
     * @param \Bss\CompanyAccount\Model\SubUserFactory $subUserFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Bss\CompanyAccount\Model\SubUserOrderRepository $subUserOrderRepository
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     */
    public function __construct(
        \Bss\CompanyAccount\Model\ResourceModel\SubUser\CollectionFactory $subUserCollectionFactory,
        \Bss\CompanyAccount\Model\SubUserFactory $subUserFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Bss\CompanyAccount\Model\SubUserOrderRepository $subUserOrderRepository,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder
    ) {
        $this->subUserCollectionFactory = $subUserCollectionFactory;
        $this->subUserFactory = $subUserFactory;
        $this->moduleManager = $moduleManager;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        $this->subUserOrderRepository = $subUserOrderRepository;
        $this->request = $request;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * Check install module QuoteExtension
     *
     * @return bool
     */
    public function isInstallQuoteExtension()
    {
        return $this->moduleManager->isEnabled('Bss_QuoteExtension');
    }

    /**
     * Check enable module QuoteExtension
     *
     * @param $websiteId
     * @return bool
     * @throws LocalizedException
     */
    public function isEnableQuoteExtension($websiteId = null)
    {
        if ($websiteId === null) {
            $websiteId = $this->storeManager->getWebsite()->getId();
        }
        $configEnable = (bool)$this->scopeConfig->getValue(
            self::XML_PATH_ENABLED_QUOTE_EXTENSION,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
        if ($configEnable && $this->isInstallQuoteExtension()) {
            return true;
        }
        return false;
    }

    /**
     * Get count quote by sub id
     *
     * @param int $subId
     * @return int
     */
    public function getQuantityOfQuoteBySubUserId($subId)
    {
        return 0;
    }

    /**
     * Get subUser
     *
     * @return \Magento\Framework\Data\Collection\AbstractDb|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection|null
     */
    public function getSubUserQuote()
    {
        $customerId = $this->customerSession->getCustomerId();
        $collection = $this->subUserCollectionFactory->create()->addFieldToFilter('customer_id', $customerId);
        $collection->getSelect()->joinLeft(
            [
                'bss_sub_user_order' => 'bss_sub_user_order'
            ],
            'main_table.sub_id = bss_sub_user_order.sub_id',
            [
                'grand_total' => 'bss_sub_user_order.grand_total',
            ]
        );
        $collection->getSelect()->columns(['count' => new \Zend_Db_Expr('COUNT(*)')])
            ->group('main_table.sub_id');
        $collection->getSelect()->columns(['grand_total' => new \Zend_Db_Expr('SUM(grand_total)')])
            ->group('main_table.sub_id');
        if ($this->request->getParam('datefrom') && $this->request->getParam('dateto')) {
            $dateFrom = $this->request->getParam('datefrom');
            $dateTo = $this->request->getParam('dateto') . ' 23:59:59';
            $collection->addFieldToFilter('main_table.created_at', ['gt' => $dateFrom])
                ->addFieldToFilter('main_table.created_at', ['lt' => $dateTo]);
        }
        return $collection;
    }
}
