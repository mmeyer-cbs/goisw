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
 * @package    Bss_QuoteExtension
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Ui\Component\Listing\Column\CompanyAccount;

use Bss\QuoteExtension\Helper\Data as HelperData;
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepareDataSource(array $dataSource)
    {
        if ($this->helperData->isEnableCompanyAccount()) {
            if (isset($dataSource['data']['items'])) {
                foreach ($dataSource['data']['items'] as & $item) {
                    if (!$item[$this->getData('name')] && $item["bss_is_company_account"]) {
                        $item[$this->getData('name')] = "";
                    }
                }
            }
        }
        return $dataSource;
    }

    /**
     * Display column Sub Name when module Bss_CompanyAccount enable
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepare()
    {
        parent::prepare();
        if (!$this->helperData->isEnableCompanyAccount()) {
            $this->_data['config']['visible'] = false;
        }
    }
}
