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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Ui\Component\Listing\Role\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Prepare actions column for customer roles grid
 */
class Actions extends Column
{
    const CUSTOMER_ROLES_PATH_DELETE = 'bss_companyaccount/customer_role/delete';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource($dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (isset($item['role_id']) && $item['customer_id'] !== null) {
                    $item[$name]['edit'] = [
                        'callback' => [
                            [
                                'provider' => 'customer_form.areas.bss_company_account_manage_role.'
                                    . 'bss_company_account_manage_role.'
                                    . 'bss_companyaccount_customer_listroles_update_modal.'
                                    . 'update_bss_companyaccount_customer_listroles_form_loader',
                                'target' => 'destroyInserted',
                            ],
                            [
                                'provider' => 'customer_form.areas.bss_company_account_manage_role.'
                                    . 'bss_company_account_manage_role.'
                                    . 'bss_companyaccount_customer_listroles_update_modal',
                                'target' => 'openModal',
                            ],
                            [
                                'provider' => 'customer_form.areas.bss_company_account_manage_role.'
                                    . 'bss_company_account_manage_role.'
                                    . 'bss_companyaccount_customer_listroles_update_modal.'
                                    . 'update_bss_companyaccount_customer_listroles_form_loader',
                                'target' => 'render',
                                'params' => [
                                    'role_id' => $item['role_id'],
                                ],
                            ]
                        ],
                        'href' => '#',
                        'label' => __('Edit'),
                        'hidden' => false,
                    ];

                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::CUSTOMER_ROLES_PATH_DELETE,
                            ['customer_id' => $item['customer_id'], 'id' => $item['role_id']]
                        ),
                        'label' => __('Delete'),
                        'isAjax' => true,
                        'confirm' => [
                            'title' => __('Delete role'),
                            'message' => __('Are you sure you want to delete the role?')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
