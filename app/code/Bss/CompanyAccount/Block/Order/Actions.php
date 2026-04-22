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
namespace Bss\CompanyAccount\Block\Order;

use Bss\CompanyAccount\Api\SubUserQuoteRepositoryInterface;
use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Helper\Tabs as TabsOrder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;

/**
 * Class Action
 */
class Actions extends \Magento\Framework\View\Element\Template
{

    /**
     * @var PermissionsChecker
     */
    protected $permissionChecker;

    /**
     * @var TabsOrder
     */
    protected $tabsHelper;

    /**
     * @var SubUserQuoteRepositoryInterface
     */
    private $subQuoteRepository;

    /**
     * Function construct
     *
     * @param PermissionsChecker $permissionChecker
     * @param TabsOrder $tabsHelper
     * @param Template\Context $context
     * @param SubUserQuoteRepositoryInterface $subQuoteRepository
     * @param array $data
     */
    public function __construct(
        PermissionsChecker     $permissionChecker,
        TabsOrder              $tabsHelper,
        Template\Context       $context,
        SubUserQuoteRepositoryInterface $subQuoteRepository,
        array                  $data = []
    ) {
        $this->permissionChecker = $permissionChecker;
        $this->tabsHelper = $tabsHelper;
        $this->subQuoteRepository = $subQuoteRepository;
        parent::__construct($context, $data);
    }

    /**
     * Function get order id
     *
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->getRequest()->getParam('order_id');
    }

    /**
     * Function check sub user can place order
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function canPlaceOrder() : bool
    {
        return $this->tabsHelper->isPlaceOrder();
    }

    /**
     * Allow sub user approve order
     *
     * @return bool
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function approveOrder()
    {
        return $this->tabsHelper->approveOrder();
    }

    /**
     * Function check sendOrderWaiting
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function sendOrderWaiting()
    {
        return $this->tabsHelper->sendOrderWaiting();
    }

    /**
     * Function check is order status waiting
     *
     * @return bool
     */
    public function isWaitingOrder(): bool
    {
        $quoteStatus = $this->subQuoteRepository->getByQuoteId($this->getOrderId())->getQuoteStatus();
        if ($quoteStatus == 'waiting') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Function check is order status rejected
     *
     * @return bool
     */
    public function isRejectedOrder(): bool
    {
        $quoteStatus = $this->subQuoteRepository->getByQuoteId($this->getOrderId())->getQuoteStatus();
        if ($quoteStatus == 'rejected') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Function check canCheckOut
     *
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function canCheckout(): bool
    {
        $id = $this->getOrderId();
        $status = $this->subQuoteRepository->getByQuoteId($id)->getQuoteStatus();
        if ($this->canPlaceOrder()) {
            if ($status == 'waiting' || $status == 'approved') {
                return true;
            }
        } elseif ($this->sendOrderWaiting() && $status == 'approved') {
            if ($this->tabsHelper->getApproveStatus($id) == 'Approved') {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
        return false;
    }
}
