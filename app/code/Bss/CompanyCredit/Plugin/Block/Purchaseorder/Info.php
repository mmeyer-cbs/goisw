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
namespace Bss\CompanyCredit\Plugin\Block\Purchaseorder;

use Bss\CompanyCredit\Helper\Data as HelperData;
use Bss\CompanyCredit\Model\ResourceModel\History\CollectionFactory as HistoryCollection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;

class Info extends \Magento\Payment\Block\Info
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var HistoryCollection
     */
    protected $historyCollection;

    /**
     * @var string
     */
    protected $_template = 'Bss_CompanyCredit::info/purchaseorder.phtml';

    /**
     * Info constructor.
     *
     * @param HelperData $helperData
     * @param HistoryCollection $historyCollection
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        HelperData $helperData,
        HistoryCollection $historyCollection,
        Template\Context $context,
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->historyCollection = $historyCollection;
        parent::__construct($context, $data);
    }

    /**
     * Is history credit
     *
     * @return bool
     * @throws LocalizedException
     */
    public function isHistoryCredit()
    {
        try {
            if ($this->getInfo() && $this->getInfo()->getId()) {
                $historyCollection = $this->historyCollection->create()
                    ->addFieldToFilter("order_id", $this->getInfo()->getParentId());
                if ($historyCollection->getSize()) {
                    return true;
                }
            }
        } catch (\Exception $exception) {
            $this->helperData->logError($exception->getMessage());
        }
        return false;
    }

    /**
     * Pass data to payment information
     *
     * @param mixed $subject
     * @param mixed $result
     * @return bool|mixed
     */
    public function afterGetSpecificInformation($subject, $result)
    {
        try {
            if ($subject->getInfo() && $subject->getInfo()->getId()) {
                $historyCollection = $this->historyCollection->create()
                    ->addFieldToFilter("order_id", $subject->getInfo()->getParentId());
                if ($historyCollection->getSize()) {
                    $result["Paid by Credit"] = null;
                }
            }
        } catch (\Exception $exception) {
            $this->helperData->logError($exception->getMessage());
        }
        return $result;
    }
}
