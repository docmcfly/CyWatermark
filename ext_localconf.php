<?php

use Cylancer\Resource\Processing\LocalCropScaleMaskHelper;



defined('TYPO3') || die();

use Cylancer\CyWatermark\Hook\WatermarkHookImpl;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;


/**
 *
 * This file is part of the "cy_watermark" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 *
 */


ExtensionManagementUtility::addTypoScript(
    'cy_watermark',
    'setup',
    "@import 'EXT:cy_watermark/Configuration/TypoScript/setup.typoscript'"
);


$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Resource\Processing\LocalCropScaleMaskHelper::class] = [
    'className' => Cylancer\CyWatermark\Resource\Processing\LocalCropScaleMaskHelper::class,
];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Resource\Processing\LocalImageProcessor::class] = [
    'className' => Cylancer\CyWatermark\Resource\Processing\LocalImageProcessor::class,
];
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Resource\Processing\LocalPreviewHelper::class] = [
    'className' => Cylancer\CyWatermark\Resource\Processing\LocalPreviewHelper::class,
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['processors']['CyWatermarkProcessor'] = [
    'className' => WatermarkHookImpl::class,
    'before' => ['LocalImageProcessor'],
];

