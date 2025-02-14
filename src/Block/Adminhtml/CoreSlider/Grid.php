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

namespace Magestore\Bannerslider\Block\Adminhtml\CoreSlider;

use Magestore\Bannerslider\Helper\Data as ModuleHelper;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Data\CollectionFactory;

/**
 * Core slider grid.
 * @category Magestore
 * @package  Magestore_Bannerslider
 * @module   Bannerslider
 * @author   Magestore Developer
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magestore\Bannerslider\Helper\Data
     */
    protected $bannersliderHelper;

    /**
     * @var \Magento\Framework\Data\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $objectFactory;

    /**
     * Class constructor.
     * 
     * Initialize class dependencies.
     *
     * @param \Magestore\Bannerslider\Helper\Data $bannersliderHelper
     * @param \Magento\Framework\Data\CollectionFactory $collectionFactory
     * @param \Magento\Framework\DataObjectFactory $objectFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        ModuleHelper $bannersliderHelper,
        CollectionFactory $collectionFactory,
        DataObjectFactory $objectFactory,
        Context $context,
        BackendHelper $backendHelper,
        array $data = []
    ) {
        $this->bannersliderHelper = $bannersliderHelper;
        $this->collectionFactory = $collectionFactory;
        $this->objectFactory = $objectFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * [_construct description].
     *
     * @return [type] [description]
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('coresliderGrid');
        $this->setDefaultSort('coreslider_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setFilterVisibility(false);
    }

    protected function _prepareCollection()
    {
        $coreSlider = $this->bannersliderHelper->getCoreSlider();

        $collection = $this->collectionFactory->create();
        foreach ($coreSlider as $slider) {
            $collection->addItem(
                $this->objectFactory->create(
                    ['data' => [
                        'id' => $slider['value'],
                        'title' => $slider['label'],
                    ]]
                )
            );
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'title',
            [
                'header' => __('List Slider'),
                'align' => 'left',
                'index' => 'title',

            ]
        );

        $this->addColumn(
            'preview',
            [
                'header' => __('Preview'),
                'align' => 'left',
                'index' => 'preview',
                'renderer' => 'Magestore\Bannerslider\Block\Adminhtml\CoreSlider\Helper\Renderer\Action',
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * get row url
     * @param  object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/slider/preview', array('sliderpreview_id' => $row->getId()));
    }
}
