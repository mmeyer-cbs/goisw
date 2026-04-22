<?php
declare(strict_types = 1);

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
 * @copyright  Copyright (c) 2020-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyAccount\Block\SubUser;

use Bss\CompanyAccount\Api\Data\SubUserInterface;
use Bss\CompanyAccount\Api\SubUserRepositoryInterface;
use Bss\CompanyAccount\Model\ResourceModel\SubUser\CollectionFactory as SubUserCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Customer\Helper\Session\CurrentCustomer;

/**
 * Class Index
 *
 * @package Bss\CompanyAccount\Block\SubUser
 */
class Index extends Template
{
    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var SubUserCollectionFactory
     */
    private $subUserCollection;

    /**
     * @var SubUserRepositoryInterface
     */
    protected $subUserRepository;

    /**
     * Index constructor.
     *
     * @param Template\Context $context
     * @param CurrentCustomer $currentCustomer
     * @param SubUserRepositoryInterface $subUserRepository
     * @param SubUserCollectionFactory $subUserCollection
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CurrentCustomer $currentCustomer,
        SubUserRepositoryInterface $subUserRepository,
        SubUserCollectionFactory $subUserCollection,
        array $data = []
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->subUserRepository = $subUserRepository;
        $this->subUserCollection = $subUserCollection;
        parent::__construct($context, $data);
    }

    /**
     * Manage sub-user constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $collection = $this->subUserCollection->create();

        $page = $this->getRequest()->getParam('p') ?: 1;
        $pageSize = $this->getRequest()->getParam('limit') ?: 10;

        $collection->addFieldToFilter('customer_id', $this->currentCustomer->getCustomerId())
            ->addOrder('sub_id', 'desc')
            ->setPageSize($pageSize)
            ->setCurPage($page);
        $this->setItems($collection);
    }

    /**
     * Enter description here...
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock(\Magento\Theme\Block\Html\Pager::class, 'companyaccount.subuser.index')
            ->setAvailableLimit([10 => 10, 20 => 20, 50 => 50])
            ->setShowPerPage(true)
            ->setCollection($this->getItems())
            ->setPath('companyaccount/subuser/');
        $this->setChild('pager', $pager);
        $this->getItems()->load();

        return $this;
    }

    /**
     * Get edit link
     *
     * @param SubUserInterface $subUser
     * @return string
     */
    public function getEditUrl($subUser)
    {
        return $this->getUrl('companyaccount/subuser/edit', ['sub_id' => $subUser->getSubId()]);
    }

    /**
     * Get create url
     *
     * @return string
     */
    public function getCreateUrl()
    {
        return $this->getUrl('companyaccount/subuser/create');
    }

    /**
     * Get delete link
     *
     * @param SubUserInterface $subUser
     * @return string
     */
    public function getDeleteUrl($subUser)
    {
        return $this->getUrl('companyaccount/subuser/delete', ['sub_id' => $subUser->getSubId()]);
    }

    /**
     * Get reset password link
     *
     * @param SubUserInterface $subUser
     *
     * @return string
     */
    public function getResetPasswordUrl($subUser)
    {
        return $this->getUrl('companyaccount/subuser/resetpassword', ['sub_id' => $subUser->getSubId()]);
    }
}
