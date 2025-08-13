<?php

defined('TYPO3') || die;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use Cylancer\CyWatermark\Domain\Model\Position;
use Cylancer\CyWatermark\Domain\Model\SourceOption;

$translationKey = 'LLL:EXT:cy_watermark/Resources/Private/Language/locallang.xlf:';

$GLOBALS['TCA']['sys_file_metadata']['palettes']['tx_cywatermark_watermark_file_settings'] = [
    'label' => $translationKey . 'sys_file_metadata.tx_cywatermark_watermark_tab',
    'showitem' => 'tx_cywatermark_watermark_source, --linebreak--,'
        . ' tx_cywatermark_watermark_position, tx_cywatermark_watermark_relative_size, --linebreak--,'
        . ' tx_cywatermark_watermark_file',
];


ExtensionManagementUtility::addTCAcolumns(
    'sys_file_metadata',
    [
        'tx_cywatermark_watermark_source' => [
            'exclude' => true,
            'label' => $translationKey . 'sys_file_metadata.tx_cywatermark_watermark_source',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [$translationKey . 'tx_cywatermark_watermark_source.none', 0], // SourceOption::NONE],
                    [$translationKey . 'tx_cywatermark_watermark_source.category', 1], //  SourceOption::CATEGORY],
                    [$translationKey . 'tx_cywatermark_watermark_source.file', 2], //  SourceOption::IMAGE],
                ],
                'default' => $translationKey . 'tx_cywatermark_watermark_source.category',
            ],
        ],

        'tx_cywatermark_watermark_file' => [
            'exclude' => true,
            'label' => $translationKey . 'sys_file_metadata.tx_cywatermark_watermark_file',
            'config' => [
                'type' => 'file',
                'appearance' => [
                    'createNewRelationLinkTitle' => $translationKey . 'add_category_watermark_file'
                ],
                'maxitems' => 1,
                'minitems' => 0,
                'allowed' => ['jpeg', 'png', 'jpg', 'bmp', 'gif', 'webp'],

            ],
        ],
        'tx_cywatermark_watermark_relative_size' => [
            //'exclude' => true,
            'label' => $translationKey . 'sys_category.tx_cywatermark_watermark_relative_size',
            'config' => [
                'type' => 'input',
                'nullable' => true,
                'default' => '25',
                // 'mode' => 'useOrOverridePlaceholder',
            ],

        ],
        'tx_cywatermark_watermark_position' => [
            'exclude' => true,
            'label' => $translationKey . 'sys_file_metadata.tx_cywatermark_watermark_position',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [$translationKey . 'tx_cywatermark_watermark_position.top_left', 0], // Position::TOP_LEFT],
                    [$translationKey . 'tx_cywatermark_watermark_position.top_right', 1], // Position::TOP_RIGHT],
                    [$translationKey . 'tx_cywatermark_watermark_position.bottom_left', 2], //  Position::BOTTOM_LEFT],
                    [$translationKey . 'tx_cywatermark_watermark_position.bottom_right', 3], // Position::BOTTOM_RIGHT],
                    [$translationKey . 'tx_cywatermark_watermark_position.center', 4], // Position::CENTER],
                    [$translationKey . 'tx_cywatermark_watermark_position.diagonal_slash', 5], // Position::DIAGONAL_SLASH],
                    [$translationKey . 'tx_cywatermark_watermark_position.diagonal_backslash', 6], // Position::DIAGONAL_BACKSLASH],
                ],
                'default' => 3,
            ],
        ],
    ]
);

ExtensionManagementUtility::addToAllTCAtypes(
    'sys_file_metadata',
    '--div--;' . $translationKey . 'sys_file_metadata.tx_cywatermark_watermark_tab;palette,--palette--;;tx_cywatermark_watermark_file_settings'

);