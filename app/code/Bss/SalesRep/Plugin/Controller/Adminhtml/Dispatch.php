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
namespace Bss\SalesRep\Plugin\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\Model\Auth;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * Class Dispatch
 *
 * @package Bss\SalesRep\Plugin\Controller\Adminhtml
 */
class Dispatch
{
    /**
     * @var SessionManagerInterface
     */
    protected $coreSession;

    /**
     * @var Auth
     */
    protected $auth;

    /**
     * Dispatch constructor.
     * @param SessionManagerInterface $coreSession
     * @param Auth $auth
     */
    public function __construct(
        SessionManagerInterface $coreSession,
        Auth $auth
    ) {
        $this->coreSession = $coreSession;
        $this->auth = $auth;
    }

    /**
     * Around Dispatch
     *
     * @param Action $subject
     * @param string $proceed
     * @param RequestInterface $request
     * @return ResponseInterface|ResultInterface
     * @throws NotFoundException
     */
    public function aroundDispatch(
        Action $subject,
        $proceed,
        RequestInterface $request
    ) {
        $idSalesRep = $this->coreSession->getIsSalesRep() ?? [];
        $module = $request->getModuleName();
        if ($this->auth->getUser() && !empty($idSalesRep)) {
            $id = $this->auth->getUser()->getId();
            $listModule = ['sales', 'customer', 'bss_quote_extension', 'loginascustomer'];
            if (in_array($module, $listModule)) {
                if (in_array($id, $idSalesRep)) {
                    // Need to preload isFirstPageAfterLogin (see https://github.com/magento/magento2/issues/15510)
                    if ($this->auth->isLoggedIn()) {
                        $this->auth->getAuthStorage()->isFirstPageAfterLogin();
                    }
                    return $subject->execute();
                }
            }
        }
        return $proceed($request);
    }
}
