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
 * @copyright  Copyright (c) 2020-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\SalesRep\Plugin\Framework;

use Bss\SalesRep\Helper\Data;
use Magento\Framework\App\RequestInterface;

class Authorization
{

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * Constructor
     *
     * @param Data $helper
     * @param RequestInterface $request
     */
    public function __construct(
        Data $helper,
        RequestInterface $request
    ) {
        $this->helper = $helper;
        $this->request = $request;
    }

    /**
     * Check permission of sales rep and shipment role
     *
     * @param \Magento\Framework\Authorization $subject
     * @param bool $result
     * @return bool|mixed
     */
    public function afterIsAllowed($subject, $result, $resource)
    {
        $isAllowed = $this->helper->getIsAllowed();
        if (isset($isAllowed[$resource])) {
            $result = true;
        }
        return $result;
    }
}
