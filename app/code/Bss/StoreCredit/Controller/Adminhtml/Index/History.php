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
 * @package    Bss_StoreCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\StoreCredit\Controller\Adminhtml\Index;

use Magento\Customer\Controller\Adminhtml\Index;
use Bss\StoreCredit\Block\Adminhtml\Edit\Tab\StoreCredit\History as BlockHistory;

/**
 * Class History
 *
 * @package Bss\StoreCredit\Controller\Adminhtml\Index
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class History extends Index
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Bss_StoreCredit::storecredit';

    /**
     * Customer store credtit history grid
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->initCurrentCustomer();
        $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock(BlockHistory::class)->toHtml()
        );
    }
}
