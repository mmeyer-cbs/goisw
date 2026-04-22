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
namespace Bss\CompanyCredit\Helper;

use Bss\CompanyCredit\Helper\Currency as HelperCurrency;
use Bss\CompanyCredit\Helper\Data as HelperData;
use Bss\CompanyCredit\Model\ResourceModel\Credit\CollectionFactory;
use Bss\CompanyCredit\Model\ResourceModel\History\CollectionFactory as HistoryCollection;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Phrase;

/**
 * Form CreditInfo default renderer
 *
 * @since 100.0.2
 */
class CreditInfo extends AbstractHelper
{

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var string
     */
    protected $currencyCode;

    /**
     * @var HelperCurrency
     */
    protected $helperCurrency;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var CollectionFactory
     */
    protected $creditCollection;

    /**
     * @var HistoryCollection
     */
    protected $historyCollection;

    /**
     * Information constructor.
     *
     * @param RequestInterface $request
     * @param HelperCurrency $helperCurrency
     * @param HelperData $helperData
     * @param CollectionFactory $creditCollection
     * @param Context $context
     */
    public function __construct(
        RequestInterface $request,
        HelperCurrency $helperCurrency,
        HelperData $helperData,
        CollectionFactory $creditCollection,
        Context $context
    ) {
        $this->request = $request;
        $this->helperCurrency = $helperCurrency;
        $this->helperData = $helperData;
        $this->creditCollection = $creditCollection;
        parent::__construct($context);
    }

    /**
     * Get credit information
     *
     * @return DataObject|null
     */
    public function getCreditInfo()
    {
        $collection = $this->creditCollection->create()
            ->addFieldToFilter(
                'customer_id',
                $this->request->getParam('id')
            );
        if ($collection->getSize()) {
            $creditInfo = $collection->getLastItem();
            $this->currencyCode = $creditInfo->getCurrencyCode();
            return $creditInfo;
        }
        return null;
    }

    /**
     * Get Format Price
     *
     * @param float $price
     * @return string
     */
    public function formatPrice(float $price)
    {
        return $this->helperCurrency->formatPrice($price, $this->currencyCode);
    }

    /**
     * Convert Yes or No
     *
     * @param string $value
     * @return Phrase
     */
    public function convertYesNo($value)
    {
        return $this->helperData->convertYesNo($value);
    }
}
