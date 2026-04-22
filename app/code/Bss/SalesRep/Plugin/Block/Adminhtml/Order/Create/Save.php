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
namespace Bss\SalesRep\Plugin\Block\Adminhtml\Order\Create;

use Bss\SalesRep\Helper\Data;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Class Save
 *
 * @package Bss\SalesRep\Plugin\Block\Adminhtml\Order\Create
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Save
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var SessionManagerInterface
     */
    protected $coreSession;

    /**
     * @var Session
     */
    protected $authSession;

    /**
     * Save constructor.
     *
     * @param Data $helper
     * @param SessionManagerInterface $coreSession
     * @param Session $authSession
     */
    public function __construct(
        Data $helper,
        SessionManagerInterface $coreSession,
        Session $authSession
    ) {
        $this->helper = $helper;
        $this->coreSession = $coreSession;
        $this->authSession = $authSession;
    }

    /**
     * Redirect after save
     *
     * @param \Magento\Sales\Controller\Adminhtml\Order\Create\Save $subject
     * @param object $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute($subject, $result)
    {
        $salesRep = $this->coreSession->getIsSalesRep() ?? [];
        $id = $this->authSession->getUser()->getId();
        if ($this->helper->isEnable() && in_array($id, $salesRep)) {
            $result->setPath('salesrep/index/order');
            return $result;

        }
        return $result;
    }
}
