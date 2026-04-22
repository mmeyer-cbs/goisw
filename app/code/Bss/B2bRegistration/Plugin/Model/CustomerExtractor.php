<?php
declare(strict_types=1);
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
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\B2bRegistration\Plugin\Model;

use Bss\B2bRegistration\Model\CustomerB2b;
use Magento\Framework\App\RequestInterface;

class CustomerExtractor
{

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var CustomerB2b
     */
    protected $customerSession;

    /**
     * @param CustomerB2b $customerSession
     * @param RequestInterface $request
     */
    public function __construct(
        CustomerB2b      $customerSession,
        RequestInterface $request
    ) {
        $this->customerSession = $customerSession;
        $this->request = $request;
    }

    /**
     * Creates a Customer object populated with the given form code and request data if enable module
     *
     * @param \Magento\Customer\Model\CustomerExtractor $subject
     * @param array|mixed $result
     */
    public function afterExtract(\Magento\Customer\Model\CustomerExtractor $subject, $result)
    {
        if ($this->customerSession->isB2bAccount()) {
            $result->setPrefix($this->request->getParam('prefix'));
            $result->setMiddleName($this->request->getParam('middlename'));
            $result->setSuffix($this->request->getParam('suffix'));
            $result->setGender($this->request->getParam('gender'));
            $result->setTaxvat($this->request->getParam('taxvat'));
        }
        return $result;
    }
}
