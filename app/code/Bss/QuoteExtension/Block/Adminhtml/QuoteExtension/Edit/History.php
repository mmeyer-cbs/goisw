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
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit;

use Bss\QuoteExtension\Helper\Json as JsonHelper;
use Bss\QuoteExtension\Helper\QuoteExtension\Version as QuoteVersionHelper;

/**
 * Class History
 *
 * @package Bss\QuoteExtension\Block\Adminhtml\QuoteExtension\Edit
 */
class History extends \Magento\Sales\Block\Adminhtml\Order\AbstractOrder
{
    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @var \Bss\QuoteExtension\Helper\Data
     */
    protected $helper;

    /**
     * @var QuoteVersionHelper
     */
    protected $versionHelper;

    /**
     * History constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param JsonHelper $jsonHelper
     * @param \Bss\QuoteExtension\Helper\Data $helper
     * @param QuoteVersionHelper $versionHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        JsonHelper $jsonHelper,
        \Bss\QuoteExtension\Helper\Data $helper,
        QuoteVersionHelper $versionHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $adminHelper,
            $data
        );
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;
        $this->versionHelper = $versionHelper;
    }

    /**
     * Retrieve quote model instance
     *
     * @return \Bss\QuoteExtension\Model\ManageQuote
     */
    public function getQuoteExtension()
    {
        return $this->_coreRegistry->registry('quoteextension_quote');
    }

    /**
     * Retrieve quote model instance
     *
     * @return \Bss\QuoteExtension\Model\QuoteExtension
     */
    public function getQuote()
    {
        return $this->_coreRegistry->registry('mage_quote');
    }

    /**
     * Get history Collection
     *
     * @return \Bss\QuoteExtension\Model\ResourceModel\QuoteVersion\Collection
     */
    public function getHistoryCollection()
    {
        $requestQuote = $this->getQuoteExtension();
        return $this->versionHelper->getHistoryCollection($requestQuote);
    }

    /**
     * Decode data version
     *
     * @param array $versionData
     * @return mixed
     */
    public function unserialize($versionData)
    {
        return $this->jsonHelper->unserialize($versionData);
    }

    /**
     * Retrieve formated price
     *
     * @param string $value
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function formatPrice($value)
    {
        return $this->helper->formatPrice($value);
    }

    /**
     * Prepare Log for items
     *
     * @param string $log
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareLogItems($log)
    {
        return $this->versionHelper->prepareLogHtml($log);
    }
}
