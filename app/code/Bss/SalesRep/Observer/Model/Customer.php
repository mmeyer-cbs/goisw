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
namespace Bss\SalesRep\Observer\Model;

use Bss\SalesRep\Helper\Data;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class Customer
 *
 * @package Bss\SalesRep\Observer\Model
 */
class Customer implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Role constructor.
     * @param RequestInterface $request
     * @param Data $helper
     */
    public function __construct(
        RequestInterface $request,
        Data $helper
    ) {
        $this->request = $request;
        $this->helper = $helper;
    }

    /**
     * Set sales representative
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $data = $this->request->getPostValue();
        if ($this->helper->isEnable() && isset($data['bss_sales_rep'])) {
            $customer = $observer->getCustomer();
            $customer->setCustomAttribute("bss_sales_representative", $data['bss_sales_rep']);
        }
    }
}
