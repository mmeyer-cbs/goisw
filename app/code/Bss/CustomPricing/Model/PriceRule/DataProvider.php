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

namespace Bss\CustomPricing\Model\PriceRule;

use Bss\CustomPricing\Api\Data\PriceRuleInterface;
use Bss\CustomPricing\Helper\Data;
use Bss\CustomPricing\Model\ResourceModel\PriceRule\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Price rule data provider
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
     * @var Data
     */
    protected $helper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * DataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $ruleCollectionFactory
     * @param \Magento\Backend\Model\Session $backendSession
     * @param Data $helper
     * @param StoreManagerInterface $storeManager
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $ruleCollectionFactory,
        \Magento\Backend\Model\Session $backendSession,
        Data $helper,
        StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $ruleCollectionFactory->create();
        $this->backendSession = $backendSession;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        if (null !== $this->loadedData) {
            return $this->loadedData;
        }
        if ($oldData = $this->backendSession->getFormData(true)) {
            $this->loadedData[$oldData["id"]]["general_information"] = $oldData;
            $this->loadedData[$oldData["id"]]["general_information"]["cant_edit_website"] = true;
            $this->backendSession->unsFormData();
        } else {
            foreach ($this->collection->getItems() as $item) {
                $this->loadedData[$item->getId()]["general_information"] = $item->getData();
                $this->loadedData[$item->getId()]["general_information"]["cant_edit_website"] = true;
                $this->loadedData[$item->getId()]['general_information']['customer_condition']['is_not_logged_rule'] = $item->getIsNotLoggedRule();

                $this->loadedData[$item->getId()]['general_information']['configs'] = [
                    PriceRuleInterface::DEFAULT_PRICE_METHOD => $item->getData(PriceRuleInterface::DEFAULT_PRICE_METHOD),
                    PriceRuleInterface::DEFAULT_PRICE_VALUE => $item->getData(PriceRuleInterface::DEFAULT_PRICE_VALUE)
                ];
                $this->loadedData[$item->getId()]["general_information"]["currencies"] = [
                    $item->getData(PriceRuleInterface::WEBSITE_ID) => $this->helper->getCurrencySymbol($item->getData(PriceRuleInterface::WEBSITE_ID))
                ];
            }
        }

        if ($this->loadedData === null) {
            $this->loadedData[""]["general_information"]["currencies"] = $this->getAllWebsiteBaseCurrencySymbol();
        }
        return $this->loadedData;
    }

    /**
     * Get base currency of all websites
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getAllWebsiteBaseCurrencySymbol()
    {
        $result = [];

        foreach ($this->storeManager->getWebsites() as $website) {
            $result[$website->getId()] = $this->helper->getCurrencySymbol($website->getId());
        }

        return $result;
    }
}
