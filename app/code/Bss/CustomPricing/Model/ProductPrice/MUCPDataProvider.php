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
namespace Bss\CustomPricing\Model\ProductPrice;

use Bss\CustomPricing\Api\PriceRuleRepositoryInterface;
use Bss\CustomPricing\Helper\Data;
use Magento\Framework\Locale\FormatInterface as LocaleFormat;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Data provider of product price for product price grid.
 *
 * @property \Bss\CustomPricing\Model\ResourceModel\ProductPrice\Collection $collection
 */
class MUCPDataProvider extends DataProvider
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * MUCPDataProvider constructor.
     *
     * @param \Bss\CustomPricing\Model\ResourceModel\ProductPrice\CollectionFactory $pPriceCollectionFac
     * @param PriceRuleRepositoryInterface $priceRuleRepository
     * @param Data $helper
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param SerializerInterface $serializer
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        \Bss\CustomPricing\Model\ResourceModel\ProductPrice\CollectionFactory $pPriceCollectionFac,
        PriceRuleRepositoryInterface $priceRuleRepository,
        Data $helper,
        $name,
        $primaryFieldName,
        $requestFieldName,
        SerializerInterface $serializer,
        LocaleFormat $localeFormat,
        array $meta = [],
        array $data = []
    ) {
        $this->request = $helper->getRequest();
        $this->serializer = $serializer;
        parent::__construct(
            $pPriceCollectionFac,
            $priceRuleRepository,
            $helper,
            $name,
            $primaryFieldName,
            $requestFieldName,
            $localeFormat,
            $meta,
            $data
        );
    }

    /**
     * @inheritDoc
     */
    public function getData(): array
    {
        $data = $this->request->getParam('data');
        $ruleId = $this->request->getParam('rule_id');
        if (null != $this->loadedData) {
            return $this->loadedData;
        }
        try {
            $currencySymbol = $this->getCurrencySymbol($ruleId);
        } catch (\Exception $e) {
            $currencySymbol = "";
        }
        $this->loadedData[$ruleId] = [
            'rule_id' => $ruleId,
            'data' => $this->serializer->serialize($data),
            'custom_price' => "",
            "currency_sym" => $currencySymbol
        ];
        return $this->loadedData;
    }
}
