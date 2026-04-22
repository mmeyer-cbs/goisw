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
 * @copyright  Copyright (c) 2020-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SalesRep\Plugin\Controller\Adminhtml;

use Bss\SalesRep\Helper\Data;
use Bss\SalesRep\Helper\Mail;
use Magento\Customer\Controller\Adminhtml\Index\Save;
use Magento\Customer\Model\Customer;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Model\User;

/**
 * Class BeforeSave
 *
 * @package Bss\SalesRep\Plugin\Controller\Adminhtml
 */
class BeforeSave
{
    const NOT_SALES_REP = 0;

    /**
     * @var Mail
     */
    protected $mail;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManagerInterface;

    /**
     * @var Redirect
     */
    protected $redirect;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * AfterSave constructor.
     * @param Mail $mail
     * @param User $user
     * @param StoreManagerInterface $storeManagerInterface
     * @param Redirect $redirect
     * @param Customer $customer
     * @param Data $helper
     */
    public function __construct(
        Mail $mail,
        User $user,
        StoreManagerInterface $storeManagerInterface,
        Redirect $redirect,
        Customer $customer,
        Data $helper
    ) {
        $this->mail = $mail;
        $this->user = $user;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->redirect = $redirect;
        $this->customer = $customer;
        $this->helper = $helper;
    }

    /**
     * Send Mail
     *
     * @param Save $subject
     * @return array
     * @throws MailException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function beforeExecute(
        Save $subject
    ) {
        $params = $subject->getRequest()->getParams();
        if ($this->helper->isEnable() && isset($params['bss_sales_rep'])) {
            $recipientEmail = $params['customer']['email'];
            $salesRepAssign = $this->user->load($params['bss_sales_rep'])->getData();
            $variables = [];
            if ($params['bss_sales_rep'] != self::NOT_SALES_REP) {
                $variables = [
                    'salesrep_name_assign' => $this->getName($salesRepAssign),
                    'user_name_assign' => $salesRepAssign['username'],
                    'user_email_assign' => $salesRepAssign['email'],
                    'customer_name' => $this->getName($params["customer"]),
                ];
            }
            if (isset($params['customer']['entity_id'])) {
                $isSalesRep = $this->helper->getSalesRepId();
                $currentCustomId = $params['customer']['entity_id'];
                $currentCustomer = $this->customer->load($currentCustomId);
                $oldSalesRepId = $currentCustomer->getBssSalesRepresentative();
                if (isset($oldSalesRepId)) {
                    if ($params['bss_sales_rep'] != $oldSalesRepId) {
                        $salesRepUnassign = $this->user
                            ->load($oldSalesRepId)->getData();
                        if ($oldSalesRepId == self::NOT_SALES_REP
                            || !in_array($oldSalesRepId, $isSalesRep)) {
                            $this->mail->sendEmailAssignSalesRep($recipientEmail, $variables);
                        } elseif ($params['bss_sales_rep'] == self::NOT_SALES_REP) {
                            $variables = [
                                'salesrep_name_unassign' => $this->getName($salesRepUnassign),
                                'user_name_unassign' => $salesRepUnassign['username'],
                                'user_email_unassign' => $salesRepUnassign['email'],
                                'customer_name' => $this->getName($params["customer"]),
                            ];
                            $this->mail->sendEmailUnassignSalesRep($recipientEmail, $variables);
                        } else {
                            $variables = [
                                'salesrep_name_assign' => $this->getName($salesRepAssign),
                                'user_name_assign' => $salesRepAssign['username'],
                                'user_email_assign' => $salesRepAssign['email'],
                                'user_name_unassign' => $salesRepUnassign['username'],
                                'salesrep_name_unassign' => $this->getName($salesRepUnassign),
                                'user_email_unassign' => $salesRepUnassign['email'],
                                'customer_name' => $this->getName($params["customer"]),
                            ];
                            $this->mail->sendEmailUnassignSalesRep($recipientEmail, $variables);
                            $this->mail->sendEmailAssignSalesRep($recipientEmail, $variables);
                        }
                    }
                }
            } elseif (isset($params['bss_sales_rep']) && $params['bss_sales_rep'] != self::NOT_SALES_REP) {
                $this->mail->sendEmailAssignSalesRep($recipientEmail, $variables);
            }
        }
        return [];
    }

    /**
     * Get name from first name and last name
     *
     * @param array $data
     * @return string
     */
    public function getName($data)
    {
        return $data["firstname"] . " " . $data["lastname"];
    }

}
