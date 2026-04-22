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
 * @package    Bss_CatalogPermission
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CatalogPermission\Model\Config\Source;

use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class BssListCmsPage
 */
class BssListCmsPage extends AbstractSource
{
    const SIGN_IN = 'sign-in';
    const NONE = 'none';
    const CUSTOM_URL = 'custom-url';

    /**
     * @var \Magento\Cms\Model\ResourceModel\Page\CollectionFactory
     */
    protected $pageCollectionFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * BssListCmsPage constructor.
     * @param CollectionFactory $pageCollectionFactory
     * @param ManagerInterface $messageManager
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        CollectionFactory $pageCollectionFactory,
        ManagerInterface $messageManager,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->messageManager = $messageManager;
        $this->pageCollectionFactory = $pageCollectionFactory;
        $this->request = $request;
    }

    /**
     * To option array template
     *
     * @return array
     */
    public function getAllOptions()
    {
        $data = [];
        try {
            $listPage = $this->pageCollectionFactory->create()->addFieldToFilter('is_active', ['eq' => 1]);
            if (!empty($this->request->getParam('store'))) {
                $listPage->addStoreFilter($this->request->getParam('store'));
            }
            foreach ($listPage as $page) {
                /** @var \Magento\Cms\Model\Page $page */
                $data[] = [
                    'value' => $page->getId(),
                    'label' => $page->getTitle()
                ];
            }
            $data[] = [
                'value' => self::SIGN_IN,
                'label' => __('Sign-in Page')
            ];
            $data[] = [
                'value' => self::NONE,
                'label' => __('None')
            ];
            $data[] = [
                'value' => self::CUSTOM_URL,
                'label' => __('Custom Url')
            ];
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__($exception->getMessage()));
        }

        return $data;
    }
}
