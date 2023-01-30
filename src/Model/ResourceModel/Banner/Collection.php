<?php

/**
 * Magestore
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Bannerslider
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Bannerslider\Model\ResourceModel\Banner;

use Magestore\Bannerslider\Model\Banner as Model;
use Magestore\Bannerslider\Model\ResourceModel\Banner as ResourceModel;
use Magestore\Bannerslider\Model\Slider;
use Magestore\Bannerslider\Model\Status;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Psr\Log\LoggerInterface;

/**
 * Banner Collection
 */
class Collection extends AbstractCollection
{
    protected $_idFieldName = 'banner_id';

    /**
     * store view id.
     *
     * @var int
     */
    protected $storeViewId = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var array
     */
    protected $addedTable = [];

    /**
     * @var bool
     */
    protected $isLoadSliderTitle = FALSE;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $stdTimezone;

    /**
     * @var \Magestore\Bannerslider\Model\Slider
     */
    protected $slider;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }

    /**
     * Class constructor.
     * 
     * Initialize class dependencies.
     *
     * @param \Magestore\Bannerslider\Model\Slider $slider
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $stdTimezone
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        Slider $slider,
        StoreManagerInterface $storeManager,
        Timezone $stdTimezone,
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        EventManagerInterface $eventManager,
        ?AdapterInterface $connection = null,
        ?AbstractDb $resource = null
    ) {
        $this->slider = $slider;
        $this->storeManager = $storeManager;
        $this->stdTimezone = $stdTimezone;
        if ($storeViewId = $this->storeManager->getStore()->getId()) {
            $this->storeViewId = $storeViewId;
        }

        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Setter for isLoadSliderTitle.
     *
     * @param bool $isLoadSliderTitle
     * @return \Magestore\Bannerslider\Model\ResourceModel\Banner\Collection
     */
    public function setIsLoadSliderTitle(bool $isLoadSliderTitle)
    {
        $this->isLoadSliderTitle = (bool) $isLoadSliderTitle;
        return $this;
    }

    /**
     * Getter for isLoadSliderTitle
     *
     * @return bool
     */
    public function isLoadSliderTitle(): bool
    {
        return (bool) $this->isLoadSliderTitle;
    }

    /**
     * Before load collection.
     *
     * @return \Magestore\Bannerslider\Model\ResourceModel\Banner\Collection
     */
    protected function _beforeLoad()
    {
        if ($this->isLoadSliderTitle()) {
            $this->joinSliderTitle();
        }

        return parent::_beforeLoad();
    }

    /**
     * join table to get Slider Title of Banner
     * @return \Magestore\Bannerslider\Model\ResourceModel\Banner\Collection
     */
    public function joinSliderTitle()
    {
        $this->getSelect()->joinLeft(
            ['sliderTable' => $this->getTable('magestore_bannersliderslider')],
            'main_table.slider_id = sliderTable.slider_id',
            ['title' => 'sliderTable.title', 'slider_status' => 'sliderTable.status']
        );

        return $this;
    }

    /**
     * Set order random by banner id.
     *
     * @return \Magestore\Bannerslider\Model\ResourceModel\Banner\Collection
     */
    public function setOrderRandByBannerId()
    {
        $this->getSelect()->orderRand('main_table.banner_id');
        return $this;
    }

    /**
     * Get store view id.
     *
     * @return int
     */
    public function getStoreViewId()
    {
        return $this->storeViewId;
    }

    /**
     * Set store view id.
     *
     * @param int $storeViewId
     */
    public function setStoreViewId($storeViewId)
    {
        $this->storeViewId = $storeViewId;
        return $this;
    }

    /**
     * Multi store view.
     *
     * @param string|array $field
     * @param null|string|array $condition
     */
    public function addFieldToFilter($field, $condition = null)
    {
        $attributes = array(
            'name',
            'status',
            'click_url',
            'target',
            'image_alt',
            'maintable',
        );
        $storeViewId = $this->getStoreViewId();

        if (in_array($field, $attributes) && $storeViewId) {
            if (!in_array($field, $this->addedTable)) {
                $sql = sprintf(
                    'main_table.banner_id = %s.banner_id AND %s.store_id = %s  AND %s.attribute_code = %s ',
                    $this->getConnection()->quoteTableAs($field),
                    $this->getConnection()->quoteTableAs($field),
                    $this->getConnection()->quote($storeViewId),
                    $this->getConnection()->quoteTableAs($field),
                    $this->getConnection()->quote($field)
                );

                $this->getSelect()
                    ->joinLeft(array($field => $this->getTable('magestore_bannerslider_value')), $sql, array());
                $this->addedTable[] = $field;
            }

            $fieldNullCondition = $this->_translateCondition("$field.value", ['null' => TRUE]);
            $mainfieldCondition = $this->_translateCondition("main_table.$field", $condition);
            $fieldCondition = $this->_translateCondition("$field.value", $condition);
            $condition = $this->_implodeCondition(
                $this->_implodeCondition($fieldNullCondition, $mainfieldCondition, 'AND'),
                $fieldCondition,
                'OR'
            );

            $this->_select->where($condition, NULL, \Magento\Framework\DB\Select::TYPE_CONDITION);

            return $this;
        }
        if ($field == 'store_id') {
            $field = 'main_table.banner_id';
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Implode condition.
     *
     * @param mixed $firstCondition
     * @param mixed $secondCondition
     * @param string $type
     * @return string
     */
    protected function _implodeCondition($firstCondition, $secondCondition, $type)
    {
        return '(' . implode(') ' . $type . ' (', [$firstCondition, $secondCondition]) . ')';
    }

    /**
     * Get database connnection.
     * 
     * @return \Magento\Framework\DB\Adapter\AdapterInterface|false
     */
    public function getConnection()
    {
        return $this->getResource()->getConnection();
    }

    /**
     * After collection load.
     * 
     * @return \Magestore\Bannerslider\Model\ResourceModel\Banner\Collection
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        if ($storeViewId = $this->getStoreViewId()) {
            foreach ($this->_items as $item) {
                $item->setStoreViewId($storeViewId)->getStoreViewValue();
            }
        }

        return $this;
    }

    /**
     * Get banner collection.
     *
     * @param int $sliderId
     * @return \Magestore\Bannerslider\Model\ResourceModel\Banner\Collection
     */
    public function getBannerCollection($sliderId)
    {
        $storeViewId = $this->storeManager->getStore()->getId();
        $dateTimeNow = $this->stdTimezone->date()->format('Y-m-d H:i:s');

        /** @var \Magestore\Bannerslider\Model\ResourceModel\Banner\Collection $bannerCollection */
        $bannerCollection = $this->setStoreViewId($storeViewId)
            ->addFieldToFilter('slider_id', $sliderId)
            ->addFieldToFilter('status', Status::STATUS_ENABLED)
            ->addFieldToFilter('start_time', ['lteq' => $dateTimeNow])
            ->addFieldToFilter('end_time', ['gteq' => $dateTimeNow])
            ->setOrder('order_banner', 'ASC');

        if ($this->slider->getSortType() == Slider::SORT_TYPE_RANDOM) {
            $bannerCollection->setOrderRandByBannerId();
        }

        return $bannerCollection;
    }
}
