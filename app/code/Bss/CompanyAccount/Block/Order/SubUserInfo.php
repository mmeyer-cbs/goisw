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
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CompanyAccount\Block\Order;

use Bss\CompanyAccount\Helper\SubUserHelper;
use Bss\CompanyAccount\Model\SubUserQuoteRepository as SubUserQuote;
use Magento\Framework\View\Element\Template;

/**
 * Class SubUserInfo
 */
class SubUserInfo extends Template
{
    /**
     * @var SubUserQuote
     */
    protected $subUserQuote;

    /**
     * @var SubUserHelper
     */
    protected $subUser;

    /**
     * Function construct sub-user info
     *
     * @param Template\Context $context
     * @param SubUserQuote $subUserQuote
     * @param SubUserHelper $subUser
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        SubUserQuote     $subUserQuote,
        SubUserHelper    $subUser,
        array            $data = []
    ) {
        $this->subUserQuote = $subUserQuote;
        $this->subUser = $subUser;
        parent::__construct($context, $data);
    }

    /**
     * Get SubUser information
     *
     * @return bool|\Bss\CompanyAccount\Api\Data\SubUserInterface
     * @throws \Bss\CompanyAccount\Exception\RelationMethodNotFoundException
     */
    public function getSubUserInfo()
    {
        $quoteId = $this->getRequest()->getParam('order_id');
        $subUserId = $this->subUserQuote->getByQuoteId($quoteId)->getSubId();
        if ($subUserId && $subUserId !== '0') {
            $subUsers = $this->subUser->getListBy($subUserId);
            foreach ($subUsers as $subUser) {
                return $subUser;
            }
        }
        return false;
    }
}
