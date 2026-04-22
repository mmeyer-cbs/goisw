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
namespace Bss\QuoteExtension\Controller\Quote\Ajax;

/**
 * Class UpdateQuote
 *
 * @package Bss\QuoteExtension\Controller\Quote\Ajax
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class UpdateQuote extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Bss\QuoteExtension\Model\QuoteItem
     */
    protected $quoteItem;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * UpdateQuote constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Bss\QuoteExtension\Model\QuoteItem $quoteItem
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Bss\QuoteExtension\Model\QuoteItem $quoteItem
    ) {
        $this->quoteItem = $quoteItem;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Update Quote Execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $resultJson = $this->resultJsonFactory->create();
        if (isset($params['sessionProductKey']) && $params['sessionProductKey'] == 'description') {
            $data = [];
            if (isset($params['itemId'])) {
                try {
                    $this->quoteItem->load($params['itemId'], 'item_id');
                    if ($this->quoteItem->getId()) {
                        $this->quoteItem->setComment($params['value'])->save();
                    } else {
                        $data['item_id'] = $params['itemId'];
                        $data['comment'] = $params['value'];
                        $this->quoteItem->setData($data)->save();
                    }
                } catch (\Exception $e) {
                    return $resultJson->setData(['error' => [$e->getMessage()]]);
                }
            }
        }
        return $resultJson->setData(['success' => ['done']]);
    }
}
