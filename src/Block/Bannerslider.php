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

namespace Magestore\Bannerslider\Block;

use Magestore\Bannerslider\Model\ResourceModel\Banner\CollectionFactory as BannerCollectionFactory;
use Magestore\Bannerslider\Model\ResourceModel\Slider\CollectionFactory as SliderCollectionFactory;
use Magestore\Bannerslider\Model\ResourceModel\Slider\Collection as SliderCollection;
use Magestore\Bannerslider\Model\Slider as SliderModel;
use Magestore\Bannerslider\Model\Status;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;

/**
 * Bannerslider Block
 * @category Magestore
 * @package  Magestore_Bannerslider
 * @module   Bannerslider
 * @author   Magestore Developer
 */
class Bannerslider extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Magestore_Bannerslider::bannerslider.phtml';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magestore\Bannerslider\Model\ResourceModel\Slider\CollectionFactory
     */
    protected $sliderCollectionFactory;

    /**
     * @var \Magestore\Bannerslider\Model\ResourceModel\Banner\CollectionFactory
     */
    protected $bannerCollectionFactory;

    /**
     * Class constructor.
     *
     * Initialize class dependencies.
     *
     * @param \Magestore\Bannerslider\Model\ResourceModel\Slider\CollectionFactory $sliderCollectionFactory
     * @param \Magestore\Bannerslider\Model\ResourceModel\Banner\CollectionFactory $bannerCollectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        SliderCollectionFactory $sliderCollectionFactory,
        BannerCollectionFactory $bannerCollectionFactory,
        Registry $coreRegistry,
        Context $context,
        array $data = []
    ) {
        $this->sliderCollectionFactory = $sliderCollectionFactory;
        $this->bannerCollectionFactory = $bannerCollectionFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _toHtml()
    {
        $store = $this->_storeManager->getStore()->getId();

        if ($this->_scopeConfig->getValue(SliderModel::XML_CONFIG_BANNERSLIDER, ScopeInterface::SCOPE_STORE, $store)) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * Add child block slider.
     *
     * @param \Magestore\Bannerslider\Model\ResourceModel\Slider\Collection $sliderCollection
     * @return \Magestore\Bannerslider\Block\Bannerslider
     */
    public function appendChildBlockSliders(SliderCollection $sliderCollection)
    {
        foreach ($sliderCollection as $slider) {
            $this->append(
                $this->getLayout()
                    ->createBlock('Magestore\Bannerslider\Block\SliderItem')
                    ->setSliderId($slider->getId())
            );
        }

        return $this;
    }

    /**
     * set position for banner slider.
     *
     * @param mixed string|array $position
     */
    public function setPosition($position)
    {
        $sliderCollection = $this->sliderCollectionFactory
            ->create()
            ->addFieldToFilter('position', $position)
            ->addFieldToFilter('status', Status::STATUS_ENABLED);
        $this->appendChildBlockSliders($sliderCollection);

        return $this;
    }

    /**
     * Set position for banner slider.
     *
     * @param mixed|string|array $position
     */
    public function setCategoryPosition($position)
    {
        $sliderCollection = $this->sliderCollectionFactory
            ->create()
            ->addFieldToFilter('position', $position)
            ->addFieldToFilter('status', Status::STATUS_ENABLED);
        $category = $this->_coreRegistry->registry('current_category');
        if (!is_null($category)) {
            $categoryPathIds = $category->getPathIds();

            foreach ($sliderCollection as $slider) {
                $sliderCategoryIds = explode(',', $slider->getCategoryIds());
                if (count(array_intersect($categoryPathIds, $sliderCategoryIds)) > 0) {
                    $this->append(
                        $this->getLayout()
                            ->createBlock('Magestore\Bannerslider\Block\SliderItem')
                            ->setSliderId($slider->getId())
                    );
                }
            }
        }

        return $this;
    }

    /**
     * Set position for note.
     */
    public function setPositionNote()
    {
        $sliderCollection = $this->sliderCollectionFactory
            ->create()
            ->addFieldToFilter('style_content', SliderModel::STYLE_CONTENT_YES)
            ->addFieldToFilter('style_slide', SliderModel::STYLESLIDE_SPECIAL_NOTE)
            ->addFieldToFilter('status', Status::STATUS_ENABLED);

        $this->appendChildBlockSliders($sliderCollection);

        return $this;
    }

    /**
     * Set popup on home page.
     */
    public function setPopupOnHomePage()
    {
        $sliderCollection = $this->sliderCollectionFactory
            ->create()
            ->addFieldToFilter('style_content', SliderModel::STYLE_CONTENT_YES)
            ->addFieldToFilter('style_slide', SliderModel::STYLESLIDE_POPUP)
            ->addFieldToFilter('status', Status::STATUS_ENABLED);
        $this->appendChildBlockSliders($sliderCollection);

        return $this;
    }
}
