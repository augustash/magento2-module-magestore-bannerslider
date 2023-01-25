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

namespace Magestore\Bannerslider\Block\Adminhtml\CoreSlider\Helper\Renderer;

use Magestore\Bannerslider\Model\BannerFactory;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DataObject;

/**
 * renderer action.
 * @category Magestore
 * @package  Magestore_Bannerslider
 * @module   Bannerslider
 * @author   Magestore Developer
 */
class Action extends AbstractRenderer
{
    /**
     * Store manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * banner factory.
     *
     * @var \Magestore\Bannerslider\Model\BannerFactory
     */
    protected $bannerFactory;

    /**
     * Class constructor.
     *
     * Initialize class dependencies.
     *
     * @param \Magestore\Bannerslider\Model\BannerFactory $bannerFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
        BannerFactory $bannerFactory,
        StoreManagerInterface $storeManager,
        Context $context,
        array $data = []
    ) {
        $this->bannerFactory = $bannerFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Render action.
     *
     * @param \Magento\Framework\DataObject $row
     *
     * @return string
     */
    public function render(DataObject $row)
    {
        $url = $this->getUrl('*/slider/preview', ['sliderpreview_id' => $row->getId()]);

        $html = \sprintf(
            '<a onclick="window.open(\'%s\', \'_blank\',\'width=1000,height=700,resizable=1,scrollbars=1\'); return false;">Preview</a>',
            $url
        );
        return $html;
    }
}
