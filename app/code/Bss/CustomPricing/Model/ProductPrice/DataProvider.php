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

/**
 * Data provider of product price for product price grid.
 *
 * @property \Bss\CustomPricing\Model\ResourceModel\ProductPrice\Collection $collection
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var PriceRuleRepositoryInterface
     */
    protected $priceRuleRepository;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var LocaleFormat
     */
    protected $localeFormat;

    /**
     * @var \Bss\CustomPricing\Api\Data\PriceRuleInterface
     */
    protected $rule;

    /**
     * DataProvider constructor.
     *
     * @param \Bss\CustomPricing\Model\ResourceModel\ProductPrice\CollectionFactory $pPriceCollectionFac
     * @param PriceRuleRepositoryInterface $priceRuleRepository
     * @param Data $helper
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
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
        LocaleFormat $localeFormat,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $pPriceCollectionFac->create();
        $this->backendSession = $helper->getBackendSession();
        $this->priceRuleRepository = $priceRuleRepository;
        $this->helper = $helper;
        $this->localeFormat = $localeFormat;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get product price data for the form
     *
     * @return array
     */
    public function getData(): array
    {
        if (null !== $this->loadedData) {
            return $this->loadedData;
        }
        if ($oldData = $this->backendSession->getFormData(true)) {
            $this->loadedData = $oldData;
            $this->backendSession->unsFormData();
        } else {
            $items = $this->collection->getItems();
            foreach ($items as $item) {
                try {
                    $currencySymbol = $this->helper->getCurrencySymbol(
                        $this->getRule($item->getRuleId())->getWebsiteId()
                    );
                } catch (\Exception $e) {
                    $currencySymbol = "";
                }

                $pId = $item->getId();
                $this->loadedData[$pId] = $item->getData();
                $this->loadedData[$pId]['is_disabled'] = true;
                $this->loadedData[$pId]['currency_sym'] = $currencySymbol;
                $this->loadedData[$pId]['basePriceFormat'] = $this->localeFormat->getPriceFormat(
                    null,
                    $this->helper->getBaseCurrencyCode($this->getRule($item->getRuleId())->getWebsiteId())
                );
            }
        }

        return $this->loadedData;
    }

    /**
     * Get currency symbol
     *
     * @param int $ruleId
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getCurrencySymbol($ruleId)
    {
        return $this->helper->getCurrencySymbol(
            $this->getCurrencyCode($ruleId)
        );
    }

    /**
     * Get currency code by website
     *
     * @param int $ruleId
     *
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getCurrencyCode($ruleId)
    {
        $rule = $this->getRule($ruleId);
        return $this->helper->getBaseCurrencyCode($rule->getWebsiteId());
    }

    /**
     * Get price rule
     *
     * @param int $ruleId
     * @return \Bss\CustomPricing\Api\Data\PriceRuleInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getRule($ruleId)
    {
        if (!$this->rule) {
            $this->rule = $this->priceRuleRepository->getById($ruleId);
        }

        return $this->rule;
    }
}
