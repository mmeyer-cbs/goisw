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
namespace Bss\CompanyAccount\Block\Adminhtml\Edit\Role;

use Bss\CompanyAccount\Api\SubRoleRepositoryInterface;
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
     * @var SubRoleRepositoryInterface
     */
    private $roleRepository;

    /**
     * @param UrlInterface $urlBuilder
     * @param RequestInterface $request
     * @param SubRoleRepositoryInterface $roleRepository
     */
    public function __construct(
        UrlInterface $urlBuilder,
        RequestInterface $request,
        SubRoleRepositoryInterface $roleRepository
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        $this->roleRepository = $roleRepository;
    }

    /**
     * Get role id
     *
     * @return int
     */
    public function getRoleId()
    {
        return (int)$this->request->getParam('role_id');
    }

    /**
     * Get customer id.
     *
     * @return int|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerId()
    {
        $roleId = (int)$this->request->getParam('role_id');

        $role = $this->roleRepository->getById($roleId);

        return $role->getCompanyAccount() ?: null;
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
