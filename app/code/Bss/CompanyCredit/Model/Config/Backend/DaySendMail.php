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
 * @package    Bss_CompanyCredit
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CompanyCredit\Model\Config\Backend;

class DaySendMail extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Bss\CompanyCredit\Helper\Email
     */
    protected $emailHelper;

    /**
     * @var \Bss\CompanyCredit\Model\UpdateRemind
     */
    protected $updateRemind;

    /**
     * Construct.
     *
     * @param \Bss\CompanyCredit\Model\UpdateRemind $updateRemind
     * @param \Bss\CompanyCredit\Helper\Email $emailHelper
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Bss\CompanyCredit\Model\UpdateRemind $updateRemind,
        \Bss\CompanyCredit\Helper\Email $emailHelper,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->updateRemind = $updateRemind;
        $this->emailHelper = $emailHelper;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     * @throws \Exception
     */
    public function afterSave()
    {
        try {
            if ($this->emailHelper->getDaySendMailBeforeOverdue() != $this->getValue()) {
                $this->updateRemind->updateMultiple($this->getValue());
            }
        } catch (\Exception $e) {
            throw new \Exception(__('We can\'t save new option.'));
        }

        return parent::afterSave();
    }
}
