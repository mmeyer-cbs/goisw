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
namespace Bss\CompanyAccount\Block\Customer\Tab;

use Bss\CompanyAccount\Helper\Data;
use Magento\Customer\Block\Account\SortLinkInterface;
use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\View\Element\Html\Link\Current;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class SubChangePassword
 *
 * @package Bss\CompanyAccount\Block\Customer\Tab
 */
class ManageSubUser extends Current implements SortLinkInterface
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * SubChangePassword constructor.
     *
     * @param Context $context
     * @param DefaultPathInterface $defaultPath
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $defaultPath, $data);
    }

    /**
     * Produce and return block's html output
     *
     * If logged in is sub-user will show this tab
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function toHtml()
    {
        if ($this->helper->isCompanyAccount() &&
            $this->helper->isEnable($this->helper->getStoreManager()->getWebsite()->getId())
        ) {
            return parent::toHtml();
        }
        return '';
    }

    /**
     * Get sort order for block.
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
