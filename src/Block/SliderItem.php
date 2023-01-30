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

use Magestore\Bannerslider\Helper\Data as ModuleHelper;
use Magestore\Bannerslider\Model\Banner;
use Magestore\Bannerslider\Model\ResourceModel\Banner\CollectionFactory as BannerCollectionFactory;
use Magestore\Bannerslider\Model\Slider as SliderModel;
use Magestore\Bannerslider\Model\SliderFactory;
use Magestore\Bannerslider\Model\Status;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Slider item.
 * @category Magestore
 * @package  Magestore_Bannerslider
 * @module   Bannerslider
 * @author   Magestore Developer
 */
class SliderItem extends Template
{
    /**
     * Template for evolution slider.
     */
    public const STYLESLIDE_EVOLUTION_TEMPLATE = 'Magestore_Bannerslider::slider/evolution.phtml';

    /**
     * Template for popup.
     */
    public const STYLESLIDE_POPUP_TEMPLATE = 'Magestore_Bannerslider::slider/popup.phtml';

    /**
     * Template for note slider.
     */
    public const STYLESLIDE_SPECIAL_NOTE_TEMPLATE = 'Magestore_Bannerslider::slider/special/note.phtml';

    /**
     * Template for flex slider.
     */
    public const STYLESLIDE_FLEXSLIDER_TEMPLATE = 'Magestore_Bannerslider::slider/flexslider.phtml';

    /**
     * Template for custom slider.
     */
    public const STYLESLIDE_CUSTOM_TEMPLATE = 'Magestore_Bannerslider::slider/custom.phtml';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_stdlibDateTime;

    /**
     * @var \Magestore\Bannerslider\Model\SliderFactory
     */
    protected $sliderFactory;

    /**
     * @var \Magestore\Bannerslider\Model\Slider
     */
    protected $slider;

    /**
     * @var int
     */
    protected $sliderId;

    /**
     * @var \Magestore\Bannerslider\Helper\Data
     */
    protected $bannersliderHelper;

    /**
     * @var \Magestore\Bannerslider\Model\ResourceModel\Banner\CollectionFactory
     */
    protected $bannerCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $_stdTimezone;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Class constructor.
     *
     * Initialize class dependencies.
     *
     * @param \Magestore\Bannerslider\Model\SliderFactory $sliderFactory
     * @param \Magestore\Bannerslider\Model\Slider $slider
     * @param \Magestore\Bannerslider\Model\ResourceModel\Banner\CollectionFactory $bannerCollectionFactory
     * @param \Magestore\Bannerslider\Helper\Data $bannersliderHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $stdlibDateTime
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $_stdTimezone
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        SliderFactory $sliderFactory,
        SliderModel $slider,
        BannerCollectionFactory $bannerCollectionFactory,
        ModuleHelper $bannersliderHelper,
        DateTime $stdlibDateTime,
        Timezone $_stdTimezone,
        StoreManagerInterface $storeManager,
        Context $context,
        array $data = []
    ) {
        $this->sliderFactory = $sliderFactory;
        $this->slider = $slider;
        $this->bannersliderHelper = $bannersliderHelper;
        $this->bannerCollectionFactory = $bannerCollectionFactory;
        $this->_stdlibDateTime = $stdlibDateTime;
        $this->_stdTimezone = $_stdTimezone;
        $this->storeManager = $storeManager;
        $this->_scopeConfig = $context->getScopeConfig();

        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        $store = $this->storeManager->getStore()->getId();

        $configEnable = $this->_scopeConfig->getValue(
            SliderModel::XML_CONFIG_BANNERSLIDER,
            ScopeInterface::SCOPE_STORE,
            $store
        );

        if (
            !$configEnable
            || $this->slider->getStatus() === Status::STATUS_DISABLED
            || !$this->slider->getId()
            || !$this->getBannerCollection()->getSize()
        ) {
            return '';
        }

        return parent::_toHtml();
    }

    /**
     * Set slider Id and set template.
     *
     * @param int $sliderId
     * @return \Magestore\Bannerslider\Block\SliderItem
     */
    public function setSliderId($sliderId)
    {
        $this->sliderId = $sliderId;

        $slider = $this->sliderFactory->create()->load($this->sliderId);
        if ($slider->getId()) {
            $this->setSlider($slider);

            if ($slider->getStyleContent() == SliderModel::STYLE_CONTENT_NO) {
                $this->setTemplate(self::STYLESLIDE_CUSTOM_TEMPLATE);
            } else {
                $this->setStyleSlideTemplate($slider->getStyleSlide());
            }
        }

        return $this;
    }

    /**
     * Set style slide template.
     *
     * @param int $styleSlideId
     * @return string
     */
    public function setStyleSlideTemplate($styleSlideId)
    {
        switch ($styleSlideId) {
            // Evolution slide
            case SliderModel::STYLESLIDE_EVOLUTION_ONE:
            case SliderModel::STYLESLIDE_EVOLUTION_TWO:
            case SliderModel::STYLESLIDE_EVOLUTION_THREE:
            case SliderModel::STYLESLIDE_EVOLUTION_FOUR:
                $this->setTemplate(self::STYLESLIDE_EVOLUTION_TEMPLATE);
                break;

            case SliderModel::STYLESLIDE_POPUP:
                $this->setTemplate(self::STYLESLIDE_POPUP_TEMPLATE);
                break;
            
            // Note all page
            case SliderModel::STYLESLIDE_SPECIAL_NOTE:
                $this->setTemplate(self::STYLESLIDE_SPECIAL_NOTE_TEMPLATE);
                break;

            // Flex slide
            default:
                $this->setTemplate(self::STYLESLIDE_FLEXSLIDER_TEMPLATE);
                break;
        }
    }

    /**
     * Check if title should be shown.
     *
     * @return bool
     */
    public function isShowTitle()
    {
        return $this->slider->getShowTitle() == SliderModel::SHOW_TITLE_YES ? TRUE : FALSE;
    }

    /**
     * Get banner collection of slider.
     *
     * @return \Magestore\Bannerslider\Model\ResourceModel\Banner\Collection
     */
    public function getBannerCollection()
    {
        $sliderId = $this->slider->getId();

        /** @var \Magestore\Bannerslider\Model\ResourceModel\Banner\Collection $bannerCollection */
        $bannerCollection = $this->bannerCollectionFactory->create();
        return $bannerCollection->getBannerCollection($sliderId);
    }

    /**
     * Get first banner.
     *
     * @return \Magestore\Bannerslider\Model\Banner
     */
    public function getFirstBannerItem()
    {
        return $this->getBannerCollection()
            ->setPageSize(1)
            ->setCurPage(1)
            ->getFirstItem();
    }

    /**
     * Get position note.
     *
     * @return string
     */
    public function getPositionNote()
    {
        return $this->slider->getPositionNoteCode();
    }

    /**
     * Set slider model.
     *
     * @param \Magestore\Bannerslider\Model\Slider $slider
     * @return \Magestore\Bannerslider\Block\SliderItem
     */
    public function setSlider(\Magestore\Bannerslider\Model\Slider $slider)
    {
        $this->slider = $slider;

        return $this;
    }

    /**
     * @return \Magestore\Bannerslider\Model\Slider
     */
    public function getSlider()
    {
        return $this->slider;
    }

    /**
     * Get banner image url.
     *
     * @param \Magestore\Bannerslider\Model\Banner $banner
     * @return string
     */
    public function getBannerImageUrl(Banner $banner)
    {
        return $this->bannersliderHelper->getBaseUrlMedia($banner->getImage());
    }

    /**
     * Get flexslider html id.
     *
     * @return string
     */
    public function getFlexsliderHtmlId()
    {
        $value = \sprintf(
            'magestore-bannerslider-flex-slider-%s%s',
            $this->getSlider()->getId(),
            $this->_stdlibDateTime->gmtTimestamp()
        );

        return $value;
    }
}
