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
 * @copyright  Copyright (c) 2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Plugin\Checkout\Helper;

use Bss\CompanyAccount\Helper\Data as HelperData;
use Bss\CompanyAccount\Helper\PermissionsChecker;
use Bss\CompanyAccount\Model\Config\Source\Permissions;
use Bss\CompanyAccount\Api\SubUserQuoteRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;

/**
 * Class Data
 *
 * @package Bss\CompanyAccount\Plugin\Checkout\Helper
 */
class Data
{
    /**
     * @var HelperData
     */
    private $helper;

    /**
     * @var PermissionsChecker
     */
    private $permissionsChecker;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var SubUserQuoteRepositoryInterface
     */
    private $subUserQuoteRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Data constructor.
     *
     * @param HelperData $helper
     * @param PermissionsChecker $permissionsChecker
     * @param SubUserQuoteRepositoryInterface $subUserQuoteRepository
     * @param RequestInterface $request
     */
    public function __construct(
        HelperData $helper,
        PermissionsChecker $permissionsChecker,
        SubUserQuoteRepositoryInterface $subUserQuoteRepository,
        RequestInterface $request
    ) {
        $this->helper = $helper;
        $this->customerSession = $this->helper->getCustomerSession();
        $this->permissionsChecker = $permissionsChecker;
        $this->subUserQuoteRepository = $subUserQuoteRepository;
        $this->request = $request;
    }

    /**
     * Disable onepage checkout if sub-user max order amount is invalid
     *
     * @param \Magento\Checkout\Helper\Data $subject
     * @param $result
     * @return false|mixed
     */
    public function afterCanOnepageCheckout(
        \Magento\Checkout\Helper\Data $subject,
        $result
    ) {
        try {
            if ($this->helper->isEnable() && $this->customerSession->getSubUser()) {
                if ($this->permissionsChecker->isAdmin() || !$this->permissionsChecker
                        ->isDenied(Permissions::PLACE_ORDER)) {
                    return $result;
                }
                if ($this->permissionsChecker->isDenied(Permissions::PLACE_ORDER)) {
                    if ($this->permissionsChecker->isDenied(Permissions::PLACE_ORDER_WAITING)) {
                        return $result;
                    }
                    else {
                        return false;
                    }
                }
                $quoteId = $this->request->getParam('order_id');
                $isApprovedQuote = false;
                if ($this->subUserQuoteRepository->getByQuoteId($quoteId)) {
                    $isApprovedQuote = $this->subUserQuoteRepository->getByQuoteId($quoteId)->getQuoteStatus() == 'approved';
                }
                $canPlaceOrderWaiting = !$this->permissionsChecker
                    ->isDenied(Permissions::PLACE_ORDER_WAITING);
                if($canPlaceOrderWaiting && $isApprovedQuote) {
                    return $result;
                }
                return false;
            }
            return $result;
        } catch (\Exception $exception) {
            return false;
        }
    }
}
