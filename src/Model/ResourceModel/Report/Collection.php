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

namespace Magestore\Bannerslider\Model\ResourceModel\Report;

use Magestore\Bannerslider\Model\Report as Model;
use Magestore\Bannerslider\Model\ResourceModel\Report as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection
{
    public const REPORT_TYPE_ALL_SLIDER = '1';
    public const REPORT_TYPE_PER_SLIDER = '2';

    protected $_idFieldName = 'report_id';

    /**
     * store view id.
     *
     * @var int
     */
    protected $_storeViewId = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var array
     */
    protected $_addedTable = [];

    /**
     * @var bool
     */
    protected $_isLoadSliderTitle = FALSE;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $_stdTimezone;

    /**
     * _contruct
     *
     * @return void
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
        StoreManagerInterface $storeManager,
        Timezone $stdTimezone,
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        EventManagerInterface $eventManager,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_stdTimezone = $stdTimezone;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    /**
     * Load slider and banner information to report.
     */
    public function reportClickAndImpress($reportType)
    {
        $this->getSelect()->joinLeft(
            ['table_banner' => $this->getTable('magestore_bannerslider_banner')],
            'main_table.banner_id = table_banner.banner_id',
            ['banner_name' => 'table_banner.name', 'banner_url' => 'table_banner.click_url']
        )->joinLeft(
            ['table_slider' => $this->getTable('magestore_bannerslider_slider')],
            'main_table.slider_id = table_slider.slider_id',
            ['slider_title' => 'table_slider.title']
        )->columns('SUM(main_table.clicks) AS banner_click')
            ->columns('SUM(main_table.impmode) AS banner_impress');

        if ($reportType == self::REPORT_TYPE_ALL_SLIDER) {
            $this->getSelect()->group('main_table.banner_id');
        } else if ($reportType == self::REPORT_TYPE_PER_SLIDER) {
            $this->getSelect()->group('main_table.slider_id')
                ->group('main_table.banner_id');
        }

        return $this;
    }
}
