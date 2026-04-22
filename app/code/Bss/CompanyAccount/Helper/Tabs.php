<?php
declare(strict_types = 1);

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
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyAccount\Helper;

use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Bss\CompanyAccount\Model\ResourceModel\SubUserQuote\CollectionFactory as SubQuoteFactory;
use Bss\CompanyAccount\Model\SubUserFactory;
use Bss\CompanyAccount\Api\SubRoleRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Setup\Exception;
use Psr\Log\LoggerInterface;

/**
 * Tabs Sales Order History
 *
 * @api
 * @since 100.0.2
 */
class Tabs
{
    /**
     * Custom Date format
     */
    const FORMAT_DATE = 'n/j/Y';

    /**
     * @var SubUserFactory
     */
    protected $subUserFactory;

    /**
     * @var SubQuoteFactory
     */
    protected $subQuoteFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var PermissionsChecker
     */
    private $permissionsChecker;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var DateTimeFactory
     */
    protected $dateTimeFactory;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @var SubRoleRepositoryInterface
     */
    protected $roleRepo;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Session $customerSession
     * @param PermissionsChecker $permissionsChecker
     * @param SubQuoteFactory $subQuoteFactory
     * @param SubUserFactory $subUserFactory
     * @param Data $dataHelper
     * @param RequestInterface $request
     * @param DateTimeFactory $dateTimeFactory
     * @param TimezoneInterface $timezone
     * @param UrlInterface $urlInterface
     * @param SubRoleRepositoryInterface $roleRepo
     */
    public function __construct(
        Session                    $customerSession,
        PermissionsChecker         $permissionsChecker,
        SubQuoteFactory           $subQuoteFactory,
        SubUserFactory             $subUserFactory,
        Data                       $dataHelper,
        RequestInterface           $request,
        DateTimeFactory            $dateTimeFactory,
        TimezoneInterface          $timezone,
        UrlInterface               $urlInterface,
        LoggerInterface $logger,
        SubRoleRepositoryInterface $roleRepo
    ) {
        $this->customerSession = $customerSession;
        $this->permissionsChecker = $permissionsChecker;
        $this->subQuoteFactory = $subQuoteFactory;
        $this->subUserFactory = $subUserFactory;
        $this->dataHelper = $dataHelper;
        $this->request = $request;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->timezone = $timezone;
        $this->urlInterface = $urlInterface;
        $this->roleRepo = $roleRepo;
        $this->logger = $logger;
    }

    /**
     * Get customer quotes
     *
     * @param $status
     * @return \Bss\CompanyAccount\Model\ResourceModel\SubUserQuote\Collection|false
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQuotes($status)
    {
        $subId = $this->permissionsChecker->getSubId();
        if (!$this->permissionsChecker->isAdmin()
            && !($subId)
        ) {
            return false;
        }
        $page = ($this->request->getParam('p')) ? $this->request->getParam('p') : 1;
        $pageSize = ($this->request->getParam('limit')) ? $this->request->getParam('limit') : 10;
        $sortField = $this->request->getParam('field');
        $sortDirection = $this->request->getParam('sort');
        $customerId = $this->customerSession->getCustomerId();
        $subQuote = $this->subQuoteFactory->create()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('quote_status', $status)
            ->setPageSize($pageSize)
            ->setCurPage($page);
        if ($sortField) {
            $subQuote->setOrder($sortField, $sortDirection);
        }
        if ($this->permissionsChecker->check(Permissions::VIEW_ALL_ORDER)
        ) {
            return $subQuote->addFieldToFilter('sub_id', $subId);
        }
        return $subQuote;
    }

    /**
     * Function format currency
     *
     * @param $price
     * @return float|string
     */
    public function formatCurrency($price)
    {
        return $this->dataHelper->convertFormatCurrency($price);
    }

    /**
     * Define who created/approved/rejected order
     *
     * @param $subId
     * @return mixed|string
     */
    public function actionBy($subId)
    {
        return $this->subUserFactory->create()->load($subId, 'sub_id')->getSubName();
    }

    /**
     * Allow sub user approve order
     *
     * @return bool
     * @throws LocalizedException
     */
    public function approveOrder()
    {
        try {
            if ($this->permissionsChecker->check(Permissions::APPROVE_ORDER_WAITING)) {
                return false;
            }
            return true;
        } catch (NoSuchEntityException $e) {
            $this->logger->critical($e);
            return false;
        }
    }

    /**
     * Get message for no quotes.
     *
     * @return \Magento\Framework\Phrase
     * @since 102.1.0
     */
    public function getEmptyQuotesMessage()
    {
        return __('There are no orders here.');
    }

    /**
     * Display status of approve tab
     *
     * @param $id
     * @return string
     * @throws NoSuchEntityException
     */
    public function getApproveStatus($id)
    {
        try {
            $check = $this->dataHelper->getQuoteById($id)->getReservedOrderId();
            if ($check !== null) {
                return 'Ordered';
            } else {
                return 'Approved';
            }
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException(__($e->getMessage()));
        }
    }

    /**
     * Get format date
     *
     * @param $time
     * @return string
     * @throws \Exception
     *
     * @throws NoSuchEntityException
     */
    public function getFormatDate($time): string
    {
        try {
            return $this->timezone->date(new \DateTime($time))->format(self::FORMAT_DATE);
        } catch (Exception $e) {
            throw new NoSuchEntityException(__($e->getMessage()));
        }
    }

    /**
     * Set sort order for tabs
     *
     * @return string
     */
    public function setSortOrder()
    {
        return (strpos($this->urlInterface->getCurrentUrl(), 'desc')) ? 'asc' : 'desc';
    }

    /**
     * Identify sorting in-use
     *
     * @return string
     */
    public function getSortIcon()
    {
        if (strpos($this->urlInterface->getCurrentUrl(), 'desc')) {
            return '&uarr;';
        } elseif (strpos($this->urlInterface->getCurrentUrl(), 'asc')) {
            return '&darr;';
        } else {
            return '';
        }
    }

    /**
     * Function check permission place order
     *
     * @return bool
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function isPlaceOrder()
    {
        return !$this->permissionsChecker->isDenied(Permissions::PLACE_ORDER);
    }

    /**
     * Function check permission place order waiting
     *
     * @return bool
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function sendOrderWaiting()
    {
        return !$this->permissionsChecker->isDenied(Permissions::PLACE_ORDER_WAITING);
    }

    /**
     * Sub-user can access
     *
     * @param int $roleId
     * @return bool
     * @throws NoSuchEntityException
     */
    public function canViewTabs(int $roleId): bool
    {
        $subRole = $this->roleRepo->getById($roleId);
        $value = [Permissions::VIEW_ALL_ORDER, Permissions::PLACE_ORDER_WAITING, Permissions::APPROVE_ORDER_WAITING];
        $roleTypes = explode(',', $subRole->getRoleType() ?? '');
        foreach ($roleTypes as $type) {
            if (in_array($type, $value) || $type == Permissions::ADMIN) {
                return true;
            }
        }
        return false;
    }
}
