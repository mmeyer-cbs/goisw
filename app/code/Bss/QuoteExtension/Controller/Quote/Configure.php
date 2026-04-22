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
namespace Bss\QuoteExtension\Controller\Quote;

use Bss\QuoteExtension\Model\QuoteExtension as CustomerQuoteExtension;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Configure
 *
 * @package Bss\QuoteExtension\Controller\Quote
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Configure extends \Bss\QuoteExtension\Controller\Quote
{
    /**
     * @var \Bss\QuoteExtension\Helper\QuoteExtension\Configure
     */
    protected $helperConfigure;

    /**
     * Configure constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $quoteExtensionSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerQuoteExtension $quoteExtension
     * @param \Bss\QuoteExtension\Model\ManageQuote $manageQuote
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Bss\QuoteExtension\Helper\QuoteExtension\Configure $helperConfigure
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $quoteExtensionSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerQuoteExtension $quoteExtension,
        \Bss\QuoteExtension\Model\ManageQuote $manageQuote,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Bss\QuoteExtension\Helper\QuoteExtension\Configure $helperConfigure
    ) {
        $this->helperConfigure = $helperConfigure;
        parent::__construct(
            $context,
            $scopeConfig,
            $quoteExtensionSession,
            $storeManager,
            $formKeyValidator,
            $quoteExtension,
            $manageQuote,
            $resultPageFactory
        );
    }

    /**
     * Excute Function
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        // Extract item and product to configure
        $id = (int)$this->getRequest()->getParam('id');
        $productId = (int)$this->getRequest()->getParam('product_id');
        $quoteItem = null;

        if ($id) {
            $quoteItem = $this->quoteExtensionSession->getQuoteExtension()->getItemById($id);
        }

        try {
            if (!$quoteItem || $productId != $quoteItem->getProduct()->getId()) {
                $this->messageManager->addErrorMessage(
                    __("The quote item isn't found. Verify the item and try again.")
                );
                return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('quoteextension/quote');
            }

            $params = $this->helperConfigure->createObject([]);
            $params->setCategoryId(false);
            $params->setConfigureMode(true);
            $params->setBuyRequest($quoteItem->getBuyRequest());

            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $this->helperConfigure->getProductView()
                ->prepareAndRender(
                    $resultPage,
                    $quoteItem->getProduct()->getId(),
                    $this,
                    $params
                );
            return $resultPage;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We cannot configure the product.'));
            $this->helperConfigure->getLogger()->critical($e);
            return $this->_goBack();
        }
    }
}
