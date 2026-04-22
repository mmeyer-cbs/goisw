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
 * @package    Bss_StoreCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\StoreCredit\Block\Adminhtml\Report;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class Toolbar
 */
class Toolbar extends Template
{
    /**
     * @var array
     */
    protected $allWebsites;

    /**
     * @var \Bss\StoreCredit\Model\Currency
     */
    protected $currency;

    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface
     */
    protected $websiteCollection;

    /**
     * Date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * Constructor
     *
     * @param \Bss\StoreCredit\Model\Currency $currency
     * @param \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollection
     * @param Context $context
     * @param DateTime $date
     * @param array $data
     */
    public function __construct(
        \Bss\StoreCredit\Model\Currency $currency,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websiteCollection,
        Context $context,
        DateTime $date,
        array $data = []
    ) {
        $this->currency = $currency;
        $this->websiteCollection = $websiteCollection;
        parent::__construct($context, $data);
        $this->date = $date;
    }

    /**
     * Return date periods
     *
     * @return array
     */
    public function getPeriods()
    {
        return ['day' => __('Day'), 'month' => __('Month'), 'year' => __('Year')];
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('storecredit/report/ajax');
    }

    /**
     * @return string
     */
    public function getFromDefault()
    {
        return $this->date->date('m/d/Y', $this->getToDefault() . '-15 days');
    }

    /**
     * @return string
     */
    public function getToDefault()
    {
        return $this->date->date('m/d/Y');
    }

    /**
     * Get all website
     *
     * @return array
     */
    public function getAllWebsite()
    {
        if (!$this->allWebsites) {
            $websiteCollection = $this->websiteCollection->create();
            $currency = "";
            $allWebsite = [];
            $displayAllWebsite = true;
            if ($websiteCollection->getSize() < 2) {
                return $allWebsite;
            }
            $key = 0;
            foreach ($websiteCollection as $website) {
                $allWebsite[$key]["name"] = $website->getName();
                $allWebsite[$key]["website_id"] = $website->getWebsiteId();
                $currencyWebsite = $this->currency->getCurrencyCodeByWebsite($website->getWebsiteId());
                if ($currency != "" && $currency != $currencyWebsite) {
                    $displayAllWebsite = false;
                }
                $currency = $currencyWebsite;
                $key++;
            }
            if ($displayAllWebsite) {
                $allWebsite[$key]["name"] = __("All Websites")->render();
                $allWebsite[$key]["website_id"] = null;
            }
            $this->allWebsites = $allWebsite;
        }

        return $this->allWebsites;
    }
}
