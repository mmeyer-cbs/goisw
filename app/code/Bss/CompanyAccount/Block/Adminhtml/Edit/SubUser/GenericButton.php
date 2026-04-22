<?php
declare(strict_types=1);
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
 * @package    Bss_CompanyAccount
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Block\Adminhtml\Edit\SubUser;

use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;

/**
 * Class for common code for buttons on the create/edit address form
 */
class GenericButton
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var SubUserRepositoryInterface
     */
    private $userRepository;

    /**
     * @param UrlInterface $urlBuilder
     * @param RequestInterface $request
     * @param SubUserRepositoryInterface $userRepository
     */
    public function __construct(
        UrlInterface $urlBuilder,
        RequestInterface $request,
        SubUserRepositoryInterface $userRepository
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        $this->userRepository = $userRepository;
    }

    /**
     * Get sub id
     *
     * @return int
     */
    public function getSubId()
    {
        return (int)$this->request->getParam('sub_id');
    }

    /**
     * Get customer id.
     *
     * @return int|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerId()
    {
        $userId = (int)$this->request->getParam('sub_id');

        $user = $this->userRepository->getById($userId);

        return $user->getCustomerId() ?: null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return  string
     */
    public function getUrl($route = '', array $params = []): string
    {
        return $this->urlBuilder->getUrl($route, $params);
    }
}
