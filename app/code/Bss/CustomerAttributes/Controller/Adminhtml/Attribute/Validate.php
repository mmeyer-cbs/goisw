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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\CustomerAttributes\Controller\Adminhtml\Attribute;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Product\Url;
use Magento\Customer\Model\Attribute;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;
use Magento\Framework\View\LayoutFactory;

/**
 * Class Validate
 * @SuppressWarnings(PHPMD)
 * @package Bss\CustomerAttributes\Controller\Adminhtml\Attribute
 */
class Validate extends \Magento\Backend\App\Action
{
    public const DEFAULT_MESSAGE_KEY = 'message';

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var Attribute
     */
    protected $attribute;

    /**
     * @var DataObject
     */
    protected $response;

    /**
     * @var Url
     */
    protected $productUrl;

    /**
     * Validate constructor.
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $layoutFactory
     * @param Attribute $attribute
     * @param Url $productUrl
     * @param DataObject $response
     */
    public function __construct(
        Context       $context,
        JsonFactory   $resultJsonFactory,
        LayoutFactory $layoutFactory,
        Attribute     $attribute,
        Url           $productUrl,
        DataObject    $response
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
        $this->attribute = $attribute;
        $this->response = $response;
        $this->productUrl = $productUrl;
    }

    /**
     * Validate execute
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $this->response->setError(false);
        $attributeCode = $this->getRequest()->getParam('attribute_code');
        $frontendLabel = $this->getRequest()->getParam('frontend_label');
        $attributeCode = $attributeCode ?: $this->generateCode($frontendLabel[0]);
        $attributeId = $this->getRequest()->getParam('attribute_id');
        $attributes = $this->attribute->getCollection()->addFieldToFilter("attribute_code", ['eq' => $attributeCode]);
        $count = count($attributes);
        if ($count > 0 && !$attributeId) {
            $message = strlen($this->getRequest()->getParam('attribute_code'))
                ? __('An attribute with this code already exists.')
                : __('An attribute with the same code (%1) already exists.', $attributeCode);

            $this->setMessageToResponse($this->response, [$message]);

            $this->response->setError(true);
        }
        return $this->resultJsonFactory->create()->setData($this->response);
    }

    /**
     * Set message to response object
     *
     * @param DataObject $response
     * @param string[] $messages
     * @return DataObject
     */
    private function setMessageToResponse($response, $messages)
    {
        $messageKey = $this->getRequest()->getParam('message_key', static::DEFAULT_MESSAGE_KEY);
        if ($messageKey === static::DEFAULT_MESSAGE_KEY) {
            $messages = reset($messages);
        }
        return $response->setData($messageKey, $messages);
    }

    /**
     * Generate Code
     *
     * @param string $label
     * @return bool|string
     */
    protected function generateCode($label)
    {
        $code = substr(
            preg_replace(
                '/[^a-z_0-9]/',
                '_',
                $this->productUrl->formatUrlKey($label)
            ),
            0,
            30
        );
        if (!preg_match('/^[a-z][a-z_0-9]{0,29}[a-z0-9]$/', $code)) {
            $code = 'attr_' . ($code ?: substr(time(), 0, 8));
        }
        return $code;
    }
}
