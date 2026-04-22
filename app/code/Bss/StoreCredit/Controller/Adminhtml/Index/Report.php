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

use Bss\StoreCredit\Controller\Adminhtml\StoreCredit;
use Bss\StoreCredit\Block\Adminhtml\Report\Toolbar;

/**
 * Class Report
 * @package Bss\StoreCredit\Controller\Adminhtml\Index
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Report extends StoreCredit
{
    /**
     * Customer store credtit history grid
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|null
     */
    public function execute()
    {
        $this->_initAction()->_setActiveMenu(
            'Bss_StoreCredit::storecredit'
        )->_addBreadcrumb(
            __('Report'),
            __('Report')
        )->_addContent(
            $this->_view->getLayout()
                ->createBlock(Toolbar::class)
                ->setTemplate('Bss_StoreCredit::report/toolbar.phtml')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Report'));
        $this->_view->renderLayout();
        return null;
    }
}
