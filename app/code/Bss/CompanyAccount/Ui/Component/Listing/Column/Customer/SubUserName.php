<?php
declare(strict_types=1);

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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Ui\Component\Listing\Column\Customer;

use Bss\CompanyAccount\Helper\Data as HelperData;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class SalesRep
 *
 * @package Bss\SalesRep\Ui\Component\Listing\Column\Order
 */
class SubUserName extends Column
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * CompanyAccount constructor.
     * @param HelperData $helperData
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        HelperData $helperData,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->helperData = $helperData;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Set Is Company Account in DataSource
     *
     * @param array $dataSource
     * @return array
     * @throws LocalizedException
     */
    public function prepareDataSource($dataSource)
    {
        if($this->helperData->isEnable()) {
            if (isset($dataSource['data']['items'])) {
                foreach ($dataSource['data']['items'] as & $item) {
                    if (!$item[$this->getData('name')] && $item["bss_is_company_account"]) {
                        $item[$this->getData('name')] = $item["customer_name"];
                    }
                }
            }
        }
        return $dataSource;
    }

    /**
     * Display column Sub Name when module Bss_CompanyAccount enable
     *
     * @throws LocalizedException
     */
    public function prepare()
    {
        parent::prepare();
        if ($this->helperData->isEnable()) {
            $this->_data['config']['controlVisibility'] = true;
            $this->_data['config']['visible'] = true;
        }
    }
}
