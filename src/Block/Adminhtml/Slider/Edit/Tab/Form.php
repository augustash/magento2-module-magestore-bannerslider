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

namespace Magestore\Bannerslider\Block\Adminhtml\Slider\Edit\Tab;

use Magestore\Bannerslider\Helper\Data as ModuleHelper;
use Magestore\Bannerslider\Model\Status;
use Magestore\Bannerslider\Model\Slider;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;

/**
 * Slider Form.
 * @category Magestore
 * @package  Magestore_Bannerslider
 * @module   Bannerslider
 * @author   Magestore Developer
 */
class Form extends Generic implements TabInterface
{
    const FIELD_NAME_SUFFIX = 'slider';

    /**
     * @var \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory
     */
    protected $fieldFactory;

    /**
     * @var \Magestore\Bannerslider\Helper\Data
     */
    protected $bannersliderHelper;

    /**
     * Class constructor.
     *
     * Initialize class dependencies.
     *
     * @param \Magestore\Bannerslider\Helper\Data $bannersliderHelper
     * @param \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory $fieldFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        ModuleHelper $bannersliderHelper,
        FieldFactory $fieldFactory,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        array $data = []
    ) {
        $this->bannersliderHelper = $bannersliderHelper;
        $this->fieldFactory = $fieldFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('page.title')->setPageTitle($this->getPageTitle());
    }

    /**
     * Prepare form.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $slider = $this->getSlider();
        $isElementDisabled = true;
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        /**
         * Declare dependence.
         */
        // dependence block
        $dependenceBlock = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence');

        // Dependence field map array.
        $fieldMaps = [];
        $form->setHtmlIdPrefix('page_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Slider Information')]);

        if ($slider->getId()) {
            $fieldset->addField('slider_id', 'hidden', ['name' => 'slider_id']);
        }

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => true,
                'class' => 'required-entry',
            ]
        );

        $fieldMaps['show_title'] = $fieldset->addField(
            'show_title',
            'select',
            [
                'label' => __('Show Title'),
                'title' => __('Show Title'),
                'name' => 'show_title',
                'options' => Status::getAvailableStatuses(),
                'disabled' => false,
            ]
        );

        $fieldMaps['status'] = $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Slider Status'),
                'title' => __('Slider Status'),
                'name' => 'status',
                'options' => Status::getAvailableStatuses(),
                'disabled' => false,
            ]
        );

        $fieldMaps['style_content'] = $fieldset->addField(
            'style_content',
            'select',
            [
                'label' => __('Select available Slider Styles'),
                'name' => 'style_content',
                'values' => [
                    [
                        'value' => Status::STATUS_ENABLED,
                        'label' => __('Yes'),
                    ],
                    [
                        'value' => Status::STATUS_DISABLED,
                        'label' => __('No'),
                    ],
                ],
            ]
        );

        $fieldMaps['custom_code'] = $fieldset->addField(
            'custom_code',
            'editor',
            [
                'name' => 'custom_code',
                'label' => __('Custom slider'),
                'title' => __('Custom slider'),
                'wysiwyg' => true,
                'required' => false,
            ]
        );

        $previewUrl = $this->bannersliderHelper->getBackendUrl('*/*/preview', ['_current' => false]);
        $fieldMaps['style_slide'] = $fieldset->addField(
            'style_slide',
            'select',
            [
                'label' => __('Select Slider Mode'),
                'name' => 'style_slide',
                'values' => $this->bannersliderHelper->getStyleSlider(),
                'note' => \sprintf(
                    '<a data-preview-url="%s" href="%s" target="_blank" id="style-slide-view">Preview</a>',
                    $previewUrl,
                    $previewUrl
                ),
            ]
        );

        $fieldMaps['sort_type'] = $fieldset->addField(
            'sort_type',
            'select',
            [
                'label' => __('Sort type'),
                'name' => 'sort_type',
                'values' => [
                    [
                        'value' => Slider::SORT_TYPE_RANDOM,
                        'label' => __('Random'),
                    ],
                    [
                        'value' => Slider::SORT_TYPE_ORDERLY,
                        'label' => __('Orderly'),
                    ],
                ],
            ]
        );

        $fieldMaps['width'] = $fieldset->addField(
            'width',
            'text',
            [
                'label' => __('Width'),
                'name' => 'width',
                'required' => true,
                'class' => 'required-entry validate-number validate-greater-than-zero',
            ]
        );

        $fieldMaps['height'] = $fieldset->addField(
            'height',
            'text',
            [
                'label' => __('Height'),
                'name' => 'height',
                'required' => true,
                'class' => 'required-entry validate-number validate-greater-than-zero',
            ]
        );

        $fieldMaps['animationB'] = $fieldset->addField(
            'animationB',
            'select',
            [
                'label' => __('Animation Effect'),
                'name' => 'animationB',
                'values' => $this->bannersliderHelper->getAnimationB(),
            ]
        );

        $fieldMaps['animationA'] = $fieldset->addField(
            'animationA',
            'select',
            [
                'label' => __('Animation Effect'),
                'name' => 'animationA',
                'values' => $this->bannersliderHelper->getAnimationA(),
            ]
        );

        $fieldMaps['note_color'] = $fieldset->addField(
            'note_color',
            'select',
            [
                'name' => 'note_color',
                'label' => __('Color'),
                'title' => __('Color'),
                'values' => $this->bannersliderHelper->getOptionColor(),
            ]
        );

        $fieldMaps['slider_speed'] = $fieldset->addField(
            'slider_speed',
            'text',
            [
                'label' => __('Speed'),
                'name' => 'slider_speed',
                'note' => 'milliseconds . This is the display time of a banner',
            ]
        );

        $fieldMaps['position_note'] = $fieldset->addField(
            'position_note',
            'select',
            [
                'name' => 'position_note',
                'label' => __('Position'),
                'title' => __('Position'),
                'values' => $slider->getPositionNoteOptions(),
                'note' => 'is position will be shown on all pages',
            ]
        );

        $fieldMaps['description'] = $fieldset->addField(
            'description',
            'editor',
            [
                'name' => 'description',
                'label' => __('Note\'s content'),
                'title' => __('Note\'s content'),
                'wysiwyg' => true,
                'required' => false,
            ]
        );

        $positionImage = [];
        for ($i = 1; $i <= 5; ++$i) {
            $positionImage[] = $this->getViewFileUrl("Magestore_Bannerslider::images/position/bannerslider-ex{$i}.png");
        }
        $fieldMaps['position'] = $fieldset->addField(
            'position',
            'select',
            [
                'name' => 'position',
                'label' => __('Position'),
                'title' => __('Position'),
                'values' => $this->bannersliderHelper->getBlockIdsToOptionsArray(),
                'note' => \sprintf(
                    '<a title="" data-position-image=\'%s\' data-tooltip-image="">Preview</a>',
                    json_encode($positionImage)
                ),
            ]
        );

        $fieldMaps['position_custom'] = $fieldset->addField(
            'position_custom',
            'select',
            [
                'name' => 'position_custom',
                'label' => __('Position'),
                'title' => __('Position'),
                'values' => $this->bannersliderHelper->getBlockIdsToOptionsArray(),
                'note' => \sprintf(
                    '<a title="" data-position-image=\'%s\' data-tooltip-image="">Preview</a>',
                    json_encode($positionImage)
                ),
            ]
        );

        $fieldMaps['category_ids'] = $fieldset->addField(
            'category_ids',
            'multiselect',
            [
                'label' => __('Categories'),
                'name' => 'category_ids',
                'values' => $this->bannersliderHelper->getCategoriesArray(),
            ]
        );

        /**
         * Add field map.
         */
        foreach ($fieldMaps as $fieldMap) {
            $dependenceBlock->addFieldMap($fieldMap->getHtmlId(), $fieldMap->getName());
        }

        $mappingFieldDependence = $this->getMappingFieldDependence();

        /**
         * Add field dependence.
         */
        foreach ($mappingFieldDependence as $dependence) {
            $negative = isset($dependence['negative']) && $dependence['negative'];
            if (is_array($dependence['fieldName'])) {
                foreach ($dependence['fieldName'] as $fieldName) {
                    $dependenceBlock->addFieldDependence(
                        $fieldMaps[$fieldName]->getName(),
                        $fieldMaps[$dependence['fieldNameFrom']]->getName(),
                        $this->getDependencyField($dependence['refField'], $negative)
                    );
                }
            } else {
                $dependenceBlock->addFieldDependence(
                    $fieldMaps[$dependence['fieldName']]->getName(),
                    $fieldMaps[$dependence['fieldNameFrom']]->getName(),
                    $this->getDependencyField($dependence['refField'], $negative)
                );
            }
        }

        /**
         * Add child block dependence.
         */
        $this->setChild('form_after', $dependenceBlock);

        $defaultData = [
            'width' => 400,
            'height' => 200,
            'slider_speed' => 4500,
        ];

        if (!$slider->getId()) {
            $slider->setStatus($isElementDisabled ? Status::STATUS_ENABLED : Status::STATUS_DISABLED);
            $slider->addData($defaultData);
        }

        if ($slider->hasData('animationB')) {
            $slider->setData('animationA', $slider->getData('animationB'));
        }

        if ($slider->hasData('position')) {
            $slider->setPositionCustom($slider->getPosition());
        }

        $form->setValues($slider->getData());
        $form->addFieldNameSuffix(self::FIELD_NAME_SUFFIX);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Get dependency field.
     *
     * @param mixed $refField
     * @param bool $negative
     * @param string $separator
     * @param string $fieldPrefix
     * @return \Magento\Config\Model\Config\Structure\Element\Dependency\Field
     */
    public function getDependencyField($refField, $negative = false, $separator = ',', $fieldPrefix = '')
    {
        return $this->fieldFactory->create(
            [
                'fieldData' => [
                    'value' => (string)$refField,
                    'negative' => $negative,
                    'separator' => $separator
                ],
                'fieldPrefix' => $fieldPrefix
            ]
        );
    }

    public function getMappingFieldDependence()
    {
        return [
            [
                'fieldName' => ['width', 'height'],
                'fieldNameFrom' => 'style_slide',
                'refField' => '1,2,3,4,5',
            ],
            [
                'fieldName' => 'category_ids',
                'fieldNameFrom' => 'position',
                'refField' => implode(',', [
                    'category-sidebar-right-top',
                    'category-sidebar-right-bottom',
                    'category-sidebar-left-top',
                    'category-sidebar-left-bottom',
                    'category-content-top',
                    'category-menu-top',
                    'category-menu-bottom',
                    'category-page-bottom',
                ]),
            ],
            [
                'fieldName' => [
                    'width',
                    'height',
                    'animationA',
                    'animationB',
                    'position',
                    'style_slide',
                    'sort_type',
                    'note_color',
                    'slider_speed',
                    'position_note',
                ],
                'fieldNameFrom' => 'style_content',
                'refField' => '1',
            ],
            [
                'fieldName' => 'animationA',
                'fieldNameFrom' => 'style_slide',
                'refField' => '1,2,3,4',
            ],
            [
                'fieldName' => 'animationB',
                'fieldNameFrom' => 'style_slide',
                'refField' => '7,8,9',
            ],
            [
                'fieldName' => 'position',
                'fieldNameFrom' => 'style_slide',
                'refField' => '5,6,',
                'negative' => true,
            ],
            [
                'fieldName' => 'position_custom',
                'fieldNameFrom' => 'style_content',
                'refField' => '2',
            ],
            [
                'fieldName' => 'sort_type',
                'fieldNameFrom' => 'style_slide',
                'refField' => '5,',
                'negative' => true,
            ],
            [
                'fieldName' => ['note_color', 'position_note'],
                'fieldNameFrom' => 'style_slide',
                'refField' => '6',
            ],
            [
                'fieldName' => 'slider_speed',
                'fieldNameFrom' => 'style_slide',
                'refField' => '5,10,',
                'negative' => true,
            ],
        ];
    }

    public function getSlider()
    {
        return $this->_coreRegistry->registry('slider');
    }

    public function getPageTitle()
    {
        return $this->getSlider()->getId()
            ? __("Edit Slider '%1'", $this->_escaper->escapeHtml($this->getSlider()->getTitle()))
            : __('New Slider');
    }

    /**
     * Prepare label for tab.
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Slider Information');
    }

    /**
     * Prepare title for tab.
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Slider Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
