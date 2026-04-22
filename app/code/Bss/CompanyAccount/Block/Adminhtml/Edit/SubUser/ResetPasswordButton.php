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
namespace Bss\CompanyAccount\Block\Adminhtml\Edit\SubUser;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Bss\CompanyAccount\Ui\Component\Listing\SubUser\Column\Actions;

/**
 * Delete button on edit customer address form
 */
class ResetPasswordButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get delete button data.
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->getSubId()) {
            $data = [
                'label' => __('Reset password'),
                'on_click' => '',
                'data_attribute' => [
                    'mage-init' => [
                        'Magento_Ui/js/form/button-adapter' => [
                            'actions' => [
                                [
                                    'targetName' => 'bss_companyaccount_customer_subuser_form.'
                                        . 'bss_companyaccount_customer_subuser_form',
                                    'actionName' => 'resetPasswordSubUser',
                                    'params' => [
                                        $this->getResetPasswordUrl(),
                                    ],

                                ]
                            ],
                        ],
                    ],
                ],
                'sort_order' => 30
            ];
        }
        return $data;
    }

    /**
     * Get button url.
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getResetPasswordUrl(): string
    {
        return $this->getUrl(
            Actions::CUSTOMER_SUB_USER_PATH_RESET_PASS,
            ['customer_id' => $this->getCustomerId(), 'id' => $this->getSubId()]
        );
    }
}
