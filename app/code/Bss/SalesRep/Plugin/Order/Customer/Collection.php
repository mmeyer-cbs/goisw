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

namespace Bss\SalesRep\Plugin\Order\Customer;

use Bss\SalesRep\Helper\Data;
use Magento\Backend\Model\Auth\Session;
use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollection;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Authorization;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Class CollectionFactory
 *
 * @package Bss\SalesRep\Plugin\Block\Adminhtml\Customer
 */
class Collection
{
    /**
     * Sale Rep id null
     */
    const NOT_SALES_REP = -1;

    /**
     * @var Session
     */
    protected $authSession;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var Authorization
     */
    protected $authorization;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Grid constructor.
     *
     * @param Session $authSession
     * @param Data $helper
     * @param Http $request
     */
    public function __construct(
        Session         $authSession,
        Data            $helper,
        Http            $request,
        Authorization   $authorization,
        LoggerInterface $logger
    ) {
        $this->helper = $helper;
        $this->authSession = $authSession;
        $this->request = $request;
        $this->authorization = $authorization;
        $this->logger = $logger;
    }

    /**
     * Filter grid customer of SalseRep
     *
     * @param CustomerCollection $subject
     * @param CustomerCollection $result
     * @return CustomerCollection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterAddNameToSelect($subject, $result)
    {
        if ($this->request->getFullActionName() === 'sales_order_create_index') {
            $customerAllowed = $this->authorization->isAllowed('Magento_Sales::sales');
            $userId = $this->authSession->getUser()->getId();
            if ($this->helper->isEnable() && $this->helper->checkUserIsSalesRep() && !$customerAllowed) {
                try {
                    $result->addAttributeToFilter('bss_sales_representative', $userId);
                } catch (LocalizedException $exception) {
                    $this->logger->critical($exception);
                }
                return $result;
            }
        }
        return $result;
    }
}
