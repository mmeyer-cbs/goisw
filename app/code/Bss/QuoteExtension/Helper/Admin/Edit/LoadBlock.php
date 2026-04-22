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
namespace Bss\QuoteExtension\Helper\Admin\Edit;

use Magento\Framework\Controller\Result\RawFactory;

/**
 * Class LoadBlock
 *
 * @package Bss\QuoteExtension\Helper\Admin\Edit
 */
class LoadBlock
{
    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $productHelper;

    /**
     * @var \Magento\Sales\Model\AdminOrder\Create
     */
    protected $createQuote;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * LoadBlock constructor.
     *
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param RawFactory $resultRawFactory
     * @param \Magento\Sales\Model\AdminOrder\Create $createQuote
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        \Magento\Catalog\Helper\Product $productHelper,
        RawFactory $resultRawFactory,
        \Magento\Sales\Model\AdminOrder\Create $createQuote,
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    ) {
        $this->resultRawFactory = $resultRawFactory;
        $this->productHelper = $productHelper;
        $this->createQuote = $createQuote;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Get Info Buy Request
     *
     * @param array $buyRequest
     * @param array $params
     * @return \Magento\Framework\DataObject
     */
    public function getInfoBuyRequest($buyRequest, $params)
    {
        return $this->productHelper->addParamsToBuyRequest($buyRequest, $params);
    }

    /**
     * Set content for page
     *
     * @param array $result
     * @return mixed
     */
    public function setContent($result)
    {
        return $this->resultRawFactory->create()->setContents($result);
    }

    /**
     * Init Data Object
     *
     * @param array $data
     * @return \Magento\Framework\DataObject
     */
    public function initDataObject($data)
    {
        $dataObject = $this->dataObjectFactory->create();
        $dataObject->setData($data);
        return $dataObject;
    }

    /**
     * @return \Magento\Sales\Model\AdminOrder\Create
     */
    public function createQuote()
    {
        return $this->createQuote;
    }
}
