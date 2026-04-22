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
namespace Bss\SalesRep\Plugin\View;

use Bss\SalesRep\Helper\Data;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Model\UiComponentTypeResolver;
use Psr\Log\LoggerInterface;

/**
 * Class Render
 *
 * @package Bss\SalesRep\Plugin\View
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Render extends \Magento\Ui\Controller\Adminhtml\Index\Render
{
    /**
     * @var UiComponentFactory
     */
    protected $factory;

    /**
     * @var UiComponentTypeResolver
     */
    protected $contentTypeResolver;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var SessionManagerInterface
     */
    protected $coreSession;

    /**
     * @var Auth
     */
    protected $auth;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * Render constructor.
     * @param Data $helper
     * @param Context $context
     * @param UiComponentFactory $factory
     * @param UiComponentTypeResolver $contentTypeResolver
     * @param Auth $auth
     * @param SessionManagerInterface $coreSession
     * @param JsonFactory|null $resultJsonFactory
     * @param Escaper|null $escaper
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Data $helper,
        Context $context,
        UiComponentFactory $factory,
        UiComponentTypeResolver $contentTypeResolver,
        Auth $auth,
        SessionManagerInterface $coreSession,
        JsonFactory $resultJsonFactory = null,
        Escaper $escaper = null,
        LoggerInterface $logger = null
    ) {
        parent::__construct(
            $context,
            $factory,
            $contentTypeResolver,
            $resultJsonFactory,
            $escaper,
            $logger
        );
        $this->factory = $factory;
        $this->contentTypeResolver = $contentTypeResolver;
        $this->auth = $auth;
        $this->coreSession = $coreSession;
        $this->helper = $helper;
    }

    /**
     * Render
     *
     * @param \Magento\Ui\Controller\Adminhtml\Index\Render $render
     * @param string $proceed
     * @return Json
     */
    public function aroundExecute(
        \Magento\Ui\Controller\Adminhtml\Index\Render $render,
        $proceed
    ) {
        if ($this->helper->isEnable() && $this->helper->checkUserIsSalesRep()) {
            try {
                $component = $this->factory->create($render->getRequest()->getParam('namespace'));
                $render->prepareComponent($component);
                $render->getResponse()->appendBody((string)$component->render());

                $contentType = $this->contentTypeResolver->resolve($component->getContext());
                return $render->getResponse()->setHeader('Content-Type', $contentType, true);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->critical($e);
                $result = [
                    'error' => $this->escaper->escapeHtml($e->getMessage()),
                    'errorcode' => $this->escaper->escapeHtml($e->getCode())
                ];
                $resultJson = $this->resultJsonFactory->create();
                $resultJson->setStatusHeader(
                    \Zend\Http\Response::STATUS_CODE_400,
                    \Zend\Http\AbstractMessage::VERSION_11,
                    'Bad Request'
                );
                return $resultJson->setData($result);
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $result = [
                    'error' => __('UI component could not be rendered because of system exception'),
                    'errorcode' => $this->escaper->escapeHtml($e->getCode())
                ];
                $resultJson = $this->resultJsonFactory->create();
                $resultJson->setStatusHeader(
                    \Zend\Http\Response::STATUS_CODE_400,
                    \Zend\Http\AbstractMessage::VERSION_11,
                    'Bad Request'
                );

                return $resultJson->setData($result);
            }
        } else {
            return $proceed();
        }
    }
}
