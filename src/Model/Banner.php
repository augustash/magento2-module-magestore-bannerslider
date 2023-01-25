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

namespace Magestore\Bannerslider\Model;

use Magestore\Bannerslider\Model\BannerFactory;
use Magestore\Bannerslider\Model\ValueFactory;
use Magestore\Bannerslider\Model\ResourceModel\Banner as BannerResourceModel;
use Magestore\Bannerslider\Model\ResourceModel\Banner\Collection as BannerCollection;
use Magestore\Bannerslider\Model\ResourceModel\Slider\CollectionFactory as SliderCollectionFactory;
use Magestore\Bannerslider\Model\ResourceModel\Value\CollectionFactory as ValueCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Logger\Monolog;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

/**
 * Banner Model
 * @category Magestore
 * @package  Magestore_Bannerslider
 * @module   Bannerslider
 * @author   Magestore Developer
 */
class Banner extends \Magento\Framework\Model\AbstractModel
{
    const BASE_MEDIA_PATH = 'magestore/bannerslider/images';
    const BANNER_TARGET_SELF = 0;
    const BANNER_TARGET_PARENT = 1;
    const BANNER_TARGET_BLANK = 2;

    /**
     * @var \Magestore\Bannerslider\Model\ResourceModel\Slider\CollectionFactory
     */
    protected $_sliderCollectionFactory;

    /**
     * @var int
     */
    protected $_storeViewId = null;

    /**
     * @var \Magestore\Bannerslider\Model\BannerFactory
     */
    protected $_bannerFactory;

    /**
     * @var \Magestore\Bannerslider\Model\ValueFactory
     */
    protected $_valueFactory;

    /**
     * @var \Magestore\Bannerslider\Model\ResourceModel\Value\CollectionFactory
     */
    protected $_valueCollectionFactory;

    /**
     * @var string
     */
    protected $_formFieldHtmlIdPrefix = 'page_';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Logger\Monolog
     */
    protected $_monolog;

    /**
     * Class constructor.
     *
     * Initialize class dependencies.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magestore\Bannerslider\Model\ResourceModel\Banner $resource
     * @param \Magestore\Bannerslider\Model\ResourceModel\Banner\Collection $resourceCollection
     * @param \Magestore\Bannerslider\Model\BannerFactory $bannerFactory
     * @param \Magestore\Bannerslider\Model\ValueFactory $valueFactory
     * @param \Magestore\Bannerslider\Model\ResourceModel\Slider\CollectionFactory $sliderCollectionFactory
     * @param \Magestore\Bannerslider\Model\ResourceModel\Value\CollectionFactory  $valueCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Logger\Monolog $monolog
     */
    public function __construct(
        Context $context,
        Registry $registry,
        BannerResourceModel $resource,
        BannerCollection $resourceCollection,
        BannerFactory $bannerFactory,
        ValueFactory $valueFactory,
        SliderCollectionFactory $sliderCollectionFactory,
        ValueCollectionFactory $valueCollectionFactory,
        StoreManagerInterface $storeManager,
        Monolog $monolog
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection
        );

        $this->_bannerFactory = $bannerFactory;
        $this->_valueFactory = $valueFactory;
        $this->_sliderCollectionFactory = $sliderCollectionFactory;
        $this->_valueCollectionFactory = $valueCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_monolog = $monolog;

        if ($storeViewId = $this->_storeManager->getStore()->getId()) {
            $this->_storeViewId = $storeViewId;
        }
    }

    /**
     * Get form field html id prefix.
     *
     * @return string
     */
    public function getFormFieldHtmlIdPrefix()
    {
        return $this->_formFieldHtmlIdPrefix;
    }

    /**
     * Get availabe slide.
     *
     * @return []
     */
    public function getAvailableSlides()
    {
        $option[] = [
            'value' => '',
            'label' => __('-------- Please select a slider --------'),
        ];

        $sliderCollection = $this->_sliderCollectionFactory->create();
        foreach ($sliderCollection as $slider) {
            $option[] = [
                'value' => $slider->getId(),
                'label' => $slider->getTitle(),
            ];
        }

        return $option;
    }

    /**
     * Get store attributes.
     *
     * @return array
     */
    public function getStoreAttributes()
    {
        return array(
            'name',
            'status',
            'click_url',
            'target',
            'image_alt',
            'image',
            'caption'
        );
    }

    /**
     * Get store view id.
     *
     * @return int
     */
    public function getStoreViewId()
    {
        return $this->_storeViewId;
    }

    /**
     * Set store view id.
     *
     * @param int $storeViewId
     */
    public function setStoreViewId($storeViewId)
    {
        $this->_storeViewId = $storeViewId;

        return $this;
    }

    /**
     * Before save.
     */
    public function beforeSave()
    {
        if ($this->getStoreViewId()) {
            $defaultStore = $this->_bannerFactory->create()->setStoreViewId(null)->load($this->getId());
            $storeAttributes = $this->getStoreAttributes();
            $data = $this->getData();
            foreach ($storeAttributes as $attribute) {
                if (isset($data['use_default']) && isset($data['use_default'][$attribute])) {
                    $this->setData($attribute.'_in_store', false);
                } else {
                    $this->setData($attribute.'_in_store', true);
                    $this->setData($attribute.'_value', $this->getData($attribute));
                }
                $this->setData($attribute, $defaultStore->getData($attribute));
            }
        }

        return parent::beforeSave();
    }

    /**
     * After save.
     */
    public function afterSave()
    {
        if ($storeViewId = $this->getStoreViewId()) {
            $storeAttributes = $this->getStoreAttributes();
            $collectionBanner = $this->_valueCollectionFactory->create();
            $attributeValue = $this->_valueFactory->create()
                ->loadAttributeValue($this->getId(), $storeViewId, $storeAttributes, $collectionBanner);
            foreach ($attributeValue as $model) {
                if ($this->getData($model->getData('attribute_code') . '_in_store')) {
                    try {
                        if ($model->getData('attribute_code') == 'image' && $this->getData('delete_image')) {
                            $model->delete();
                        } else {
                            $model->setValue($this->getData($model->getData('attribute_code') . '_value'))->save();
                        }
                    } catch (\Exception $e) {
                        $this->_monolog->addError($e->getMessage());
                    }
                } elseif ($model && $model->getId()) {
                    try {
                        $model->delete();
                    } catch (\Exception $e) {
                        $this->_monolog->addError($e->getMessage());
                    }
                }
            }

        }
        return parent::afterSave();
    }

    /**
     * Load info multistore.
     *
     * @param mixed  $id
     * @param string $field
     * @return $this
     */
    public function load($id, $field = null)
    {
        parent::load($id, $field);
        if ($this->getStoreViewId()) {
            $this->getStoreViewValue();
        }

        return $this;
    }

    /**
     * Get store view value.
     *
     * @param string|null $storeViewId
     * @return $this
     */
    public function getStoreViewValue($storeViewId = null)
    {
        if (!$storeViewId) {
            $storeViewId = $this->getStoreViewId();
        }
        if (!$storeViewId) {
            return $this;
        }
        $storeValues = $this->_valueCollectionFactory->create()
            ->addFieldToFilter('banner_id', $this->getId())
            ->addFieldToFilter('store_id', $storeViewId);
        foreach ($storeValues as $value) {
            $this->setData($value->getAttributeCode().'_in_store', true);
            $this->setData($value->getAttributeCode(), $value->getValue());
        }

        return $this;
    }

    /**
     * Get target value.
     *
     * @return string
     */
    public function getTargetValue()
    {
        switch ($this->getTarget()) {
            case self::BANNER_TARGET_SELF:
                return '_self';
            case self::BANNER_TARGET_PARENT:
                return '_parent';

            default:
                return '_blank';
        }
    }
}
