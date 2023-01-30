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
 * @package     MagestoreBannerslider
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Bannerslider\Block\Adminhtml\Banner\Edit\Tab;

use Magestore\Bannerslider\Model\Banner as BannerModel;
use Magestore\Bannerslider\Model\ResourceModel\Value\CollectionFactory;
use Magestore\Bannerslider\Model\Status;
use Magestore\Bannerslider\Model\SliderFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\Registry;

/**
 * Banner Edit tab.
 * @category Magestore
 * @package  MagestoreBannerslider
 * @module   Bannerslider
 * @author   Magestore Developer
 */
class Banner extends Generic implements TabInterface
{
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $objectFactory;

    /**
     * value collection factory.
     *
     * @var \Magestore\Bannerslider\Model\ResourceModel\Value\CollectionFactory
     */
    protected $valueCollectionFactory;

    /**
     * slider factory.
     *
     * @var \Magestore\Bannerslider\Model\SliderFactory
     */
    protected $sliderFactory;

    /**
     * @var \Magestore\Bannerslider\Model\Banner
     */
    protected $banner;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $wysiwygConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $dateTime;

    /**
     * Class constructor.
     *
     * Initialize class dependencies.
     *
     * @param \Magestore\Bannerslider\Model\Banner $banner
     * @param \Magestore\Bannerslider\Model\ResourceModel\Value\CollectionFactory $valueCollectionFactory
     * @param \Magestore\Bannerslider\Model\SliderFactory $sliderFactory
     * @param \Magento\Framework\DataObjectFactory $objectFactory
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        BannerModel $banner,
        CollectionFactory $valueCollectionFactory,
        SliderFactory $sliderFactory,
        DataObjectFactory $objectFactory,
        Config $wysiwygConfig,
        Timezone $dateTime,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        array $data = []
    ) {
        $this->objectFactory = $objectFactory;
        $this->banner = $banner;
        $this->valueCollectionFactory = $valueCollectionFactory;
        $this->sliderFactory = $sliderFactory;
        $this->wysiwygConfig = $wysiwygConfig;
        $this->dateTime = $dateTime;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare layout.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('page.title')->setPageTitle($this->getPageTitle());

        Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock(
                'Magestore\Bannerslider\Block\Adminhtml\Form\Renderer\Fieldset\Element',
                $this->getNameInLayout() . '_fieldset_element'
            )
        );

        return $this;
    }

    /**
     * Prepare form.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $bannerAttributes = $this->banner->getStoreAttributes();
        $bannerAttributesInStores = ['store_id' => ''];

        foreach ($bannerAttributes as $bannerAttribute) {
            $bannerAttributesInStores[$bannerAttribute . '_in_store'] = '';
        }

        $dataObj = $this->objectFactory->create(
            ['data' => $bannerAttributesInStores]
        );
        $model = $this->_coreRegistry->registry('banner');

        if ($sliderId = $this->getRequest()->getParam('current_slider_id')) {
            $model->setSliderId($sliderId);
        }

        $dataObj->addData($model->getData());

        $storeViewId = $this->getRequest()->getParam('store');

        $attributesInStore = $this->valueCollectionFactory
            ->create()
            ->addFieldToFilter('banner_id', $model->getId())
            ->addFieldToFilter('store_id', $storeViewId)
            ->getColumnValues('attribute_code');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix($this->banner->getFormFieldHtmlIdPrefix());

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Banner Information')]);

        if ($model->getId()) {
            $fieldset->addField('banner_id', 'hidden', ['name' => 'banner_id']);
        }

        $elements = [];
        $elements['name'] = $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true,
            ]
        );

        $elements['status'] = $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Banner Status'),
                'name' => 'status',
                'options' => Status::getAvailableStatuses(),
            ]
        );

        $slider = $this->sliderFactory->create()->load($sliderId);

        if ($slider->getId()) {
            $elements['slider_id'] = $fieldset->addField(
                'slider_id',
                'select',
                [
                    'label' => __('Slider'),
                    'name' => 'slider_id',
                    'values' => [
                        [
                            'value' => $slider->getId(),
                            'label' => $slider->getTitle(),
                        ],
                    ],
                ]
            );
        } else {
            $elements['slider_id'] = $fieldset->addField(
                'slider_id',
                'select',
                [
                    'label' => __('Slider'),
                    'name' => 'slider_id',
                    'values' => $model->getAvailableSlides(),
                ]
            );
        }

        $elements['image_alt'] = $fieldset->addField(
            'image_alt',
            'text',
            [
                'title' => __('Alt Text'),
                'label' => __('Alt Text'),
                'name' => 'image_alt',
                'note' => 'Used for SEO',
            ]
        );

        $wysiwygConfig = $this->wysiwygConfig->getConfig();

        $elements['caption'] = $fieldset->addField(
            'caption',
            'editor',
            [
                'title' => __('Caption'),
                'label' => __('Caption'),
                'name' => 'caption',
                'config' => $wysiwygConfig
            ]
        );

        $elements['click_url'] = $fieldset->addField(
            'click_url',
            'text',
            [
                'title' => __('URL'),
                'label' => __('URL'),
                'name' => 'click_url',
            ]
        );

        $elements['image'] = $fieldset->addField(
            'image',
            'image',
            [
                'title' => __('Banner Image'),
                'label' => __('Banner Image'),
                'name' => 'image',
                'note' => 'Allow image type: jpg, jpeg, gif, png',
            ]
        );

        $dateFormat = 'M/d/yyyy';
        $timeFormat = 'h:mm a';
        if ($dataObj->hasData('start_time')) {

            $datetime = $this->dateTime->date($dataObj->getData('start_time'), null, $this->_localeDate->getConfigTimezone());
            // $datetime = new \DateTime($dataObj->getData('start_time'));

            $dataObj->setData('start_time', $datetime);
        }

        if ($dataObj->hasData('end_time')) {
            $datetime = $this->dateTime->date($dataObj->getData('end_time'), null, $this->_localeDate->getConfigTimezone());
            // $datetime = new \DateTime($dataObj->getData('end_time'));
            $dataObj->setData('end_time', $datetime);
        }

        $style = 'color: #000;background-color: #fff; font-weight: bold; font-size: 13px;';
        $elements['start_time'] = $fieldset->addField(
            'start_time',
            'date',
            [
                'name' => 'start_time',
                'label' => __('Starting time'),
                'title' => __('Starting time'),
                'required' => true,
                'readonly' => true,
                'style' => $style,
                'class' => 'required-entry',
                'date_format' => $dateFormat,
                'time_format' => $timeFormat,
                'note' => implode(' ', [$dateFormat, $timeFormat])
            ]
        );

        $elements['end_time'] = $fieldset->addField(
            'end_time',
            'date',
            [
                'name' => 'end_time',
                'label' => __('Ending time'),
                'title' => __('Ending time'),
                'required' => true,
                'readonly' => true,
                'style' => $style,
                'class' => 'required-entry',
                'date_format' => $dateFormat,
                'time_format' => $timeFormat,
                'note' => implode(' ', [$dateFormat, $timeFormat])
            ]
        );

        $elements['target'] = $fieldset->addField(
            'target',
            'select',
            [
                'label' => __('Target'),
                'name' => 'target',
                'values' => [
                    [
                        'value' => \Magestore\Bannerslider\Model\Banner::BANNER_TARGET_SELF,
                        'label' => __('New Window with Browser Navigation'),
                    ],
                    [
                        'value' => \Magestore\Bannerslider\Model\Banner::BANNER_TARGET_PARENT,
                        'label' => __('Parent Window with Browser Navigation'),
                    ],
                    [
                        'value' => \Magestore\Bannerslider\Model\Banner::BANNER_TARGET_BLANK,
                        'label' => __('New Window without Browser Navigation'),
                    ],
                ],
            ]
        );

        foreach ($attributesInStore as $attribute) {
            if (isset($elements[$attribute])) {
                $elements[$attribute]->setStoreViewId($storeViewId);
            }
        }
        $form->addValues($dataObj->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return mixed
     */
    public function getBanner()
    {
        return $this->_coreRegistry->registry('banner');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getPageTitle()
    {
        return $this->getBanner()->getId()
            ? __("Edit Banner '%1'", $this->_escaper->escapeHtml($this->getBanner()->getName()))
            : __('New Banner');
    }

    /**
     * Prepare label for tab.
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Banner Information');
    }

    /**
     * Prepare title for tab.
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Banner Information');
    }

    /**
     * @inheritdoc
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isHidden()
    {
        return false;
    }
}
