<?php
/**
 *  BSS Commerce Co.
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the EULA
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category    BSS
 * @package     Bss_B2bPorto
 * @author      Extension Team
 * @copyright   Copyright © 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license     http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\B2bPorto\Plugin\Controller\Product;

use Bss\B2bPorto\Helper\Data;

/**
 * Class CompareWishlist
 *
 * @package Bss\B2bPorto\Plugin\Controller\Product
 */
class CompareWishlist
{
    /** @var \Magento\Framework\App\RequestInterface */
    protected $request;

    /** @var \Bss\B2bPorto\Helper\Data */
    protected $helper;

    /**
     * CompareWishlist constructor.
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Catalog\Controller\Product\Compare\Add|\Magento\Wishlist\Controller\Index\Add $action
     * @param $page
     * @return mixed
     */
    public function afterExecute($action, $page)
    {
        if ($this->helper->isMageplazaAjaxEnabled() &&
            $action->getRequest()->isAjax()
        ) {
            return '';
        }

        return $page;
    }
}
