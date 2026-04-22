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
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\StoreCredit\Controller\Adminhtml\Report;

use Bss\StoreCredit\Controller\Adminhtml\StoreCredit;

/**
 * Class Ajax
 *
 * @package Bss\StoreCredit\Controller\Adminhtml\Report
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Ajax extends StoreCredit
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $dateStart = $this->getRequest()->getParam('from');
        $dateEnd = $this->getRequest()->getParam('to');
        $dimension = $this->getRequest()->getParam('dimension');
        $websiteId = $this->getRequest()->getParam("website_id");
        $result = [];
        $result['status'] = false;
        try {
            if ($dateStart && $dateEnd) {
                $data = $this->historyFactory->create()->loadReportData($dateStart, $dateEnd, $dimension, $websiteId);
                $result['data'] = $this->jsonHelper->jsonEncode($data);
                $result['status'] = true;
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($result)
        );
    }
}
