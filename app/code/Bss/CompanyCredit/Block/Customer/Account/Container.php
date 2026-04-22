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
 * @package    Bss_CompanyCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyCredit\Block\Customer\Account;

use Bss\CompanyCredit\Api\Data\HistoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;

/**
 * Sales order history extra container block
 *
 * @api
 * @since 100.1.1
 */
class Container extends Template
{
    /**
     * @var HistoryInterface
     */
    private $history;

    /**
     * Set history
     *
     * @param HistoryInterface $history
     * @return $this
     * @since 100.1.1
     */
    public function setHistory(HistoryInterface $history)
    {
        $this->history = $history;
        return $this;
    }

    /**
     * Get history
     *
     * @return HistoryInterface
     */
    private function getHistory()
    {
        return $this->history;
    }

    /**
     * Here we set a history for children during retrieving their HTML
     *
     * @param string $alias
     * @param bool $useCache
     * @return string
     * @throws LocalizedException
     * @since 100.1.1
     */
    public function getChildHtml($alias = '', $useCache = false)
    {
        $layout = $this->getLayout();
        if ($layout) {
            $name = $this->getNameInLayout();
            foreach ($layout->getChildBlocks($name) as $child) {
                $child->setHistory($this->getHistory());
            }
        }
        return parent::getChildHtml($alias, $useCache);
    }
}
