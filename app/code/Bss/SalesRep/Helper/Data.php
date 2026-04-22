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
 * @package    Bss_SalesRep
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SalesRep\Helper;

use Magento\Framework\Module\Manager;
use Bss\SalesRep\Model\Entity\Attribute\Source\SalesRepresentive;
use Bss\SalesRep\Model\ResourceModel\SalesRep\Collection;
use Bss\SalesRep\Model\SalesRep;
use Bss\SalesRep\Model\SalesRepRepository;
use Magento\Backend\Model\Auth\Session;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @package Bss\SalesRep\Helper
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Data extends AbstractHelper
{
    const PATH_SALES_REP_ENABLED = 'bss_salesrep/general/enable';
    const XML_PATH_ENABLED_COMPANY_ACCOUNT = 'bss_company_account/general/enable';
    /**
     * @var string
     */
    private $userIsSalesRep = '';

    /**
     * @var array
     */
    protected $isAllowed = [];
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var SalesRepRepository
     */
    protected $salesRep;

    /**
     * @var Customer
     */
    protected $customerFactory;

    /**
     * @var SalesRepresentive
     */
    protected $salesRepresentive;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var SessionManagerInterface
     */
    protected $coreSession;

    /**
     * @var \Bss\SalesRep\Model\ResourceModel\SalesRepOrder\Collection
     */
    protected $orderCollection;

    /**
     * Data constructor.
     * @param Manager $manager
     * @param \Bss\SalesRep\Model\ResourceModel\SalesRepOrder\Collection $orderCollection
     * @param SessionManagerInterface $coreSession
     * @param Session $session
     * @param SalesRepresentive $salesRepresentive
     * @param SalesRepRepository $salesRep
     * @param Customer $customerFactory
     * @param Context $context
     */
    public function __construct(
        Manager $manager,
        \Bss\SalesRep\Model\ResourceModel\SalesRepOrder\Collection $orderCollection,
        SessionManagerInterface $coreSession,
        Session $session,
        SalesRepresentive $salesRepresentive,
        SalesRepRepository $salesRep,
        Customer $customerFactory,
        Context $context
    ) {
        $this->manager = $manager;
        parent::__construct($context);
        $this->salesRep = $salesRep;
        $this->customerFactory = $customerFactory;
        $this->salesRepresentive = $salesRepresentive;
        $this->session = $session;
        $this->coreSession = $coreSession;
        $this->orderCollection = $orderCollection;
    }

    /**
     * Module Enable
     *
     * @return bool
     */
    public function isEnable()
    {
        return $this->scopeConfig->isSetFlag(
            self::PATH_SALES_REP_ENABLED,
            ScopeInterface::SCOPE_STORE,
            0
        );
    }

    /**
     * Check enable module company account
     *
     * @return bool
     */
    public function isEnableCompanyAccount(){
        $configEnable = $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED_COMPANY_ACCOUNT
        );
        $installCompanyAccount = $this->manager->isEnabled('Bss_CompanyAccount');
        if($configEnable && $installCompanyAccount) {
            return true;
        }
        return false;
    }

    /**
     * To array function
     *
     * @param string $string
     * @return array
     */
    public function toArray($string)
    {
        $string = str_replace(' ', '', $string);
        $array = explode(',', $string);
        return array_filter(
            $array,
            function ($value) {
                return $value !== '';
            }
        );
    }

    /**
     * Get Customer assigned Sales Rep
     *
     * @param int $id
     * @return Collection|SalesRep
     * @throws NoSuchEntityException
     */
    public function getSalesRep($id)
    {
        $customer = $this->customerFactory->load($id);
        return $this->salesRep->getByUserId($customer['bss_sales_representative']);
    }

    /**
     * Get User is Sales Rep
     *
     * @return array
     */
    public function getSalesRepId()
    {
        $salesRep = $this->salesRepresentive->getAllOptions();
        $salesRepId = [];
        foreach ($salesRep as $item) {
            if ($item['value'] != null) {
                $salesRepId[] = $item['value'];
            }
        }
        return $salesRepId;
    }

    /**
     * Check User is Sales Rep
     *
     * @return bool
     */
    public function checkUserIsSalesRep()
    {
        if (empty($this->userIsSalesRep)) {
            $salesRep = $this->coreSession->getIsSalesRep();
            if ($this->session->getUser() && $salesRep != null) {
                $id = $this->session->getUser()->getId();
                if (in_array($id, $salesRep)) {
                    return $this->userIsSalesRep = true;
                }
            }
            return $this->userIsSalesRep = false;
        }
        return $this->userIsSalesRep;
    }

    /**
     * Get sales rep order id
     *
     * @return array
     */
    public function arrayOrderSalesRepId()
    {
        $id = $this->session->getUser()->getId();
        $orderSalesRep = $this->orderCollection
            ->addFieldToSelect('*')
            ->addFieldToFilter('user_id', $id);
        $orderIds = [];
        foreach ($orderSalesRep as $order) {
            $orderIds[] = $order['order_id'];
        }
        return $orderIds;
    }

    /**
     * Set is allowed
     *
     * @param string $resource
     * @return void
     */
    public function setIsAllowed($resource)
    {
        $this->isAllowed[$resource] = $resource;
    }

    /**
     * Get is allowed
     *
     * @return array
     */
    public function getIsAllowed()
    {
        return $this->isAllowed;
    }
}
