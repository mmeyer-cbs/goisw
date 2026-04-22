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
 * @package    Bss_SalesRep
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\SalesRep\Block\Adminhtml\Sales\Quote;

use Bss\SalesRep\Helper\Data;
use Bss\SalesRep\Model\Entity\Attribute\Source\SalesRepresentive;
use Bss\SalesRep\Model\ManageQuote;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\App\RequestInterface;

/**
 * Class SalesRep
 *
 * @package Bss\SalesRep\Block\Adminhtml\Sales\Quote
 */
class SalesRep extends Template
{
    /**
     * @var SalesRepresentive
     */
    protected $salesRepresentive;

    /**
     * @var ManageQuote
     */
    protected $manageQuote;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * SalesRep constructor.
     * @param Data $helper
     * @param ManageQuote $manageQuote
     * @param SalesRepresentive $salesRepresentive
     * @param RequestInterface $request
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Data $helper,
        ManageQuote $manageQuote,
        SalesRepresentive $salesRepresentive,
        RequestInterface $request,
        Template\Context $context,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->manageQuote = $manageQuote;
        $this->salesRepresentive = $salesRepresentive;
        parent::__construct($context, $data);
    }

    /**
     * Get name Sales Rep
     *
     * @return mixed|string
     */
    public function getNameSalesRep()
    {
        $userName = '';
        $quoteId = $this->request->getParam('entity_id');
        $quote = $this->manageQuote->load($quoteId);
        $salesReps = $this->salesRepresentive->getAllOptions();
        foreach ($salesReps as $salesRep) {
            if ($quote->getUserId() == $salesRep['value']) {
                return $salesRep['label'];
            }
        }
        return $userName;
    }
}
