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
namespace Bss\QuoteExtension\Controller\Adminhtml\Manage;

/**
 * Class AbstractController
 *
 * @package Bss\QuoteExtension\Controller\Adminhtml\Manage
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
abstract class AbstractController extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Bss\QuoteExtension\Helper\Data
     */
    protected $helper;

    /**
     * @var \Bss\QuoteExtension\Model\ManageQuote
     */
    protected $manageQuote;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface
     */
    protected $quote = null;

    /**
     * @var \Bss\QuoteExtension\Helper\QuoteExtension\Version
     */
    protected $quoteVersion;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * AbstractController constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Bss\QuoteExtension\Helper\Data $helper
     * @param \Bss\QuoteExtension\Model\ManageQuote $manageQuote
     * @param \Bss\QuoteExtension\Helper\QuoteExtension\Version $quoteVersion
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Bss\QuoteExtension\Helper\Data $helper,
        \Bss\QuoteExtension\Model\ManageQuote $manageQuote,
        \Bss\QuoteExtension\Helper\QuoteExtension\Version $quoteVersion,
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {
        parent::__construct($context);
        $this->quoteRepository = $quoteRepository;
        $this->helper = $helper;
        $this->manageQuote = $manageQuote;
        $this->quoteVersion = $quoteVersion;
        $this->backendSession = $backendSession;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * Get Quote
     *
     * @return \Magento\Quote\Api\Data\CartInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getQuote()
    {
        if ($this->quote == null) {
            $data  = $this->getRequest()->getParams();
            $this->quote = $this->quoteRepository->get($data['quote_id']);
        }

        return $this->quote;
    }
}
