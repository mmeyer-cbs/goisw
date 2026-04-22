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
 * @package    Bss_B2bRegistration
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\B2bRegistration\Controller\Router;

use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\RequestInterface;
use Bss\B2bRegistration\Helper\Data as HelperData;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CustomRouter
 */
class Custom implements RouterInterface
{
    /**
     * @var HelperData
     */
    protected $helperData;
    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var ActionFactory
     */
    protected $actionFactory;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Custom constructor.
     * @param HelperData $helperData
     * @param ResponseInterface $response
     * @param StoreManagerInterface $storeManager
     * @param ActionFactory $actionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
      HelperData $helperData,
      ResponseInterface $response,
      StoreManagerInterface $storeManager,
      ActionFactory $actionFactory,
      LoggerInterface $logger
  )
  {
      $this->helperData = $helperData;
      $this->response = $response;
      $this->storeManager = $storeManager;
      $this->actionFactory = $actionFactory;
      $this->logger = $logger;
  }

    /**
     * Custom router b2b-customer-create
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|null
     */
    public function match(RequestInterface $request)
    {
        $enable = $this->helperData->isEnable();
        if ($enable) {
            $hasLastSlash = false;
            $bbUrl = $this->helperData->getB2bUrl();
            $urlRequest = $request->getOriginalPathInfo();
            if (substr($urlRequest, -1) == '/') {
                $hasLastSlash = true;
            }
            if ($bbUrl) {
                $bbUrl = ltrim($bbUrl, '/');
                $bbUrl = rtrim($bbUrl, '/');
            } else {
                $bbUrl = '';
            }
            $urlRequest = ltrim(rtrim($urlRequest, '/'), '/');
            if ($bbUrl == $urlRequest) {
                if ($hasLastSlash) {
                    try {
                        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
                        $this->response->setRedirect($baseUrl . $urlRequest, 301)->sendResponse();
                        $request->setDispatched(false);
                        return $this->actionFactory->create(\Magento\Framework\App\Action\Redirect::class);
                    } catch (NoSuchEntityException $e) {
                        $this->logger->critical($e->getMessage());
                    }
                }
                $request->setModuleName('btwob') //module name
                ->setControllerName('account') //controller name
                ->setActionName('create')
                ->setDispatched(false);
                return $this->actionFactory->create(Forward::class);
            }
        }
        return null;
    }
}
