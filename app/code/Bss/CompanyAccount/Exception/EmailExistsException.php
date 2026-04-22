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
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Exception;

/**
 * Class EmailExistsException
 * Custom Already exists exception
 */
class EmailExistsException extends \Magento\Framework\Exception\AlreadyExistsException
{
    /**
     * Existed id data
     *
     * @var array
     */
    private $data = [];

    /**
     * Set existed entity id data
     *
     * @param array $data
     * @return EmailExistsException
     */
    public function setData($data): EmailExistsException
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get existed data
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
