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
 * @package     Magestore_BannerSlider
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Bannerslider\Ui\Component\Listing\Column;

use Magestore\Bannerslider\Model\SliderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\UrlInterface;

/**
 * @category Magestore
 * @package  Magestore_Storelocator
 * @module   Storelocator
 * @author   Magestore Developer
 */
class Slider extends \Magestore\Bannerslider\Ui\Component\Listing\Column\AbstractColumn
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magestore\Bannerslider\Model\SliderFactory
     */
    protected $_sliderFactory;

    /**
     * Class constructor.
     *
     * Initialize class dependencies.
     *
     * @param \Magestore\Bannerslider\Model\SliderFactory $sliderFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        SliderFactory $sliderFactory,
        StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_storeManager = $storeManager;
        $this->_sliderFactory = $sliderFactory;
        $this->filesystem = $filesystem;
    }

    /**
     * Prepare item.
     *
     * @param array $item
     * @return array
     */
    protected function _prepareItem(array & $item)
    {
        $slider = $this->_sliderFactory->create()->load($item[$this->getData('name')]);

        if (isset($item[$this->getData('name')])) {
            if ($item[$this->getData('name')]) {

                $item[$this->getData('name')] = sprintf(
                    '%s',
                    $slider->getTitle()
                );
            } else {
                $item[$this->getData('name')] = '';
            }
        }

        return $item;
    }
}
