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
 * @package    Bss_CustomerAttributes
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\CustomerAttributes\Block\Adminhtml\Address\Edit\Tab\Relation;

use Bss\CustomerAttributes\Helper\Customer\Grid\NotDisplay;
use Bss\CustomerAttributes\Model\SerializeData;
use Bss\CustomerAttributes\Model\HandleData;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Backend\Block\Template;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class DependentAddressAttribute extends Template
{
    /**
     * @var NotDisplay
     */
    private $notDisplayHelper;

    /**
     * @var string
     */
    protected $_template = "Bss_CustomerAttributes::customer/attribute/tab/relation/dependent-attributes.phtml";

    /**
     * @var SerializeData
     */
    private $serializer;

    /**
     * @var HandleData
     */
    private $handleData;

    /**
     * @param NotDisplay $notDisplayHelper
     * @param Template\Context $context
     * @param SerializeData $serializer
     * @param HandleData $handleData
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     * @param DirectoryHelper|null $directoryHelper
     */
    public function __construct(
        NotDisplay       $notDisplayHelper,
        Template\Context $context,
        SerializeData    $serializer,
        HandleData       $handleData,
        array            $data = [],
        ?JsonHelper      $jsonHelper = null,
        ?DirectoryHelper $directoryHelper = null
    ) {
        parent::__construct($context, $data);
        $this->notDisplayHelper = $notDisplayHelper;
        $this->serializer = $serializer;
        $this->handleData = $handleData;
    }

    /**
     * Get All Address Attributes Collection
     *
     * @return array|AbstractDb|AbstractCollection
     */
    public function getAllAttributesCollection()
    {
        return $this->notDisplayHelper->getAllAddressCollection();
    }

    /**
     * Get Attribute By id
     *
     * @return array|AbstractDb|AbstractCollection|null
     */
    public function getAttributeById()
    {
        return $this->_request->getParam("attribute_id");
    }

    /**
     * Get All Attribute Dependent Information in Be
     *
     * @param array|mixed $attributes
     * @return array
     */
    public function getAllAttributeDependentBe($attributes)
    {
        return $this->handleData->getAllAttributeDependentBe($attributes);
    }

    /**
     * Validate All Attribute Dependent BE
     *
     * @param array|mixed $blockObj
     * @param int $customerAttributeId
     * @return mixed
     */
    public function validateAllAttributeDependentBe($blockObj, $customerAttributeId)
    {
        return $this->handleData->validateAllAttributeDependentBe($blockObj, $customerAttributeId);
    }

    /**
     * Encode function
     *
     * @param mixed|array $data
     * @return bool|string
     */
    public function encodeFunction($data)
    {
        return $this->serializer->encodeFunction($data);
    }
}
