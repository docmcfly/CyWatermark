<?php


/*
 * This file is TYPO3 forke of LocalCropScaleMaskHelper.
 */

namespace Cylancer\CyWatermark\Resource\Processing;


/**
 * Helper for creating local image previews using TYPO3s image processing classes.
 */
class LocalPreviewHelper extends \TYPO3\CMS\Core\Resource\Processing\LocalPreviewHelper implements \Cylancer\CyWatermark\Resource\Processing\LocalHelperInterface
{

    /**
     * Returns the name of the helper. 
     * 
     * @return string
     */
    public function getName():string {
        return 'Preview';
    } 

}
