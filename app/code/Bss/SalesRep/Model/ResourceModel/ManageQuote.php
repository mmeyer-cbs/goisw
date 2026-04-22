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
namespace Bss\SalesRep\Model\ResourceModel;

use Magento\Framework\App\State;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Module\Manager;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Class ManageQuote
 *
 * @package Bss\SaleRep\Model\ResourceModel
 */
class ManageQuote extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var Manager
     */
    protected $moduleManager;

    /**
     * Constructor
     *
     * @param Manager $moduleManager
     * @param Context $context
     * @param CartRepositoryInterface $quoteRepository
     * @param State $state
     * @param string $connectionName
     */
    public function __construct(
        Manager $moduleManager,
        Context $context,
        CartRepositoryInterface $quoteRepository,
        State $state,
        $connectionName = null
    ) {
        $this->moduleManager = $moduleManager;
        $this->quoteRepository = $quoteRepository;
        $this->state = $state;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        if ($this->moduleManager->isEnabled('Bss_QuoteExtension')) {
            $this->_init('quote_extension', 'entity_id');
        }
    }
}
