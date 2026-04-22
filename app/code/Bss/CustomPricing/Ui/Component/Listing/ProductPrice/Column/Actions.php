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
 * @package    Bss_CustomPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CustomPricing\Ui\Component\Listing\ProductPrice\Column;

use Bss\CustomPricing\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Bss\CustomPricing\Model\Config\Source\ProductType;

/**
 * Prepare actions column for customer addresses grid
 */
class Actions extends Column
{
    const PRODUCT_PRICE_PATH_DELETE = 'custom_pricing/productPrice/removePrice';

    /**
     * @var ProductResource
     */
    protected $productResource;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @inheritDoc
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        ProductResource $productResource,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->productResource = $productResource;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if ($this->isVisible($item)) {
                    $item[$name]['edit'] = [
                        'callback' => [
                            [
                                'provider' => 'bss_price_rule_form.areas.product_price.product_price'
                                    . '.product_price_update_modal.edit_product_price_form_loader',
                                'target' => 'destroyInserted',
                            ],
                            [
                                'provider' => 'bss_price_rule_form.areas.product_price.product_price'
                                    . '.product_price_update_modal',
                                'target' => 'openModal',
                            ],
                            [
                                'provider' => 'bss_price_rule_form.areas.product_price.product_price'
                                    . '.product_price_update_modal.edit_product_price_form_loader',
                                'target' => 'render',
                                'params' => [
                                    'id' => $item['id'],
                                ],
                            ]
                        ],
                        'href' => '#',
                        'label' => __('Edit'),
                        'hidden' => false,
                    ];

                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::PRODUCT_PRICE_PATH_DELETE,
                            ['id' => $item['id']]
                        ),
                        'label' => __('Remove Custom Price'),
                        'isAjax' => true,
                        'confirm' => [
                            'title' => __('Remove Custom Price'),
                            'message' => __('Are you sure you want to remove the custom price for the product?')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }

    /**
     * Is show action
     *
     * @param array $data
     * @return bool
     */
    private function isVisible($data)
    {
        if (in_array($data["type_id"], ProductType::getNoNeedUpdatePType())) {
            return false;
        }
        return isset($data['id']) &&
            $this->productResource->isFixedPriceType($data['product_id']);
    }
}
