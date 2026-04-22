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
 * @package    Bss_B2bRegistration
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\B2bRegistration\Plugin\Model;

class Customer
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Bss\B2bRegistration\Helper\Data
     */
    protected $data;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Bss\B2bRegistration\Helper\Data $data
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Bss\B2bRegistration\Helper\Data $data
    ) {
        $this->request = $request;
        $this->data = $data;
    }

    /**
     * Change customer group id when register in frontend
     *
     * @param \Magento\Customer\Model\Customer $subject
     * @param int $result
     * @return mixed
     */
    public function afterGetGroupId(\Magento\Customer\Model\Customer $subject, $result)
    {
        if (!empty($this->request->getParam('b2b_account'))) {
            $subject->setData('group_id', $this->data->getCustomerGroup());
        }
        return $result;
    }
}
