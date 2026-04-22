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
 * @copyright  Copyright (c) 2020-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyCredit\Ui\Component\Listing\Columns\Customer\Credit;

use Bss\CompanyCredit\Helper\Currency as HelperCurrency;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class AvailableCredit extends Column
{
    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var HelperCurrency
     */
    protected $helperCurrency;

    /**
     * AvailableCredit constructor.
     *
     * @param AuthorizationInterface $authorization
     * @param HelperCurrency $helperCurrency
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        AuthorizationInterface $authorization,
        HelperCurrency $helperCurrency,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->authorization = $authorization;
        $this->helperCurrency = $helperCurrency;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Set Is Company Account in DataSource
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item["available_credit"]) && isset($item["currency_code"])) {
                    $item[$this->getData('name')] =
                        $this->helperCurrency->formatPrice($item["available_credit"], $item["currency_code"]);
                }
            }
        }
        return $dataSource;
    }

    /**
     * Display available_credit in grid customer if not permission
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function prepare()
    {
        parent::prepare();
        if ($this->authorization->isAllowed("Bss_CompanyCredit::viewCompanyCredit")) {
            $this->_data['config']['componentDisabled'] = false;
        }
    }
}
