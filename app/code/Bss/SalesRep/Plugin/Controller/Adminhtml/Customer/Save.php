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
namespace Bss\SalesRep\Plugin\Controller\Adminhtml\Customer;

use Bss\SalesRep\Helper\Data;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;

/**
 * Class Save
 *
 * @package Bss\SalesRep\Plugin\Controller\Adminhtml\Customer
 */
class Save
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * Save constructor.
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Redirect after Save Customer
     *
     * @param \Magento\Customer\Controller\Adminhtml\Index\Save $subject
     * @param mixed $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute($subject, $result)
    {
        if ($this->helper->isEnable() && $this->helper->checkUserIsSalesRep()) {
            return $result->setPath('salesrep/index/customer');
        }

        return $result;
    }
}
