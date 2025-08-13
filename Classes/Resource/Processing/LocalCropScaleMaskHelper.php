<?php

/*
 * This file is TYPO3 forke of LocalCropScaleMaskHelper.
 */

namespace Cylancer\CyWatermark\Resource\Processing;

use TYPO3\CMS\Core\Imaging\GraphicalFunctions;
use TYPO3\CMS\Core\Imaging\ImageProcessingInstructions;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\Processing\TaskInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Helper class to locally perform a crop/scale/mask task with the TYPO3 image processing classes.
 */
class LocalCropScaleMaskHelper extends \TYPO3\CMS\Core\Resource\Processing\LocalCropScaleMaskHelper implements \Cylancer\CyWatermark\Resource\Processing\LocalHelperInterface
{
    public function __construct(private readonly bool $forceCreation = false)
    {
    }

    /**
     * Returns the name of the helper.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'CropScaleMask';
    }

    /**
     * Does the heavy lifting prescribed in processTask()
     * except that the processing can be performed on any given local image
     */
    public function processWithLocalFile(TaskInterface $task, string $originalFileName): ?array
    {
        $result = null;
        $targetFile = $task->getTargetFile();
        $targetFileExtension = $task->getTargetFileExtension();

        $imageOperations = GeneralUtility::makeInstance(GraphicalFunctions::class);

        $configuration = $targetFile->getProcessingConfiguration();
        $configuration['additionalParameters'] ??= '';

        // Normal situation (no masking) - just scale the image
        if (!is_array($configuration['maskImages'] ?? null)) {
            // the result info is an array with 0=width,1=height,2=extension,3=filename
            $result = $imageOperations->resize(
                $originalFileName,
                $targetFileExtension,
                $configuration['width'] ?? '',
                $configuration['height'] ?? '',
                $configuration['additionalParameters'],
                $configuration,
                $this->forceCreation
            );
        } else {
            $temporaryFileName = $this->getFilenameForImageCropScaleMask($task);
            $maskImage = $configuration['maskImages']['maskImage'] ?? null;
            $maskBackgroundImage = $configuration['maskImages']['backgroundImage'];
            if ($maskImage instanceof FileInterface && $maskBackgroundImage instanceof FileInterface) {
                // This converts the original image to a temporary PNG file during all steps of the masking process
                $tempFileInfo = $imageOperations->resize(
                    $originalFileName,
                    'png',
                    $configuration['width'] ?? '',
                    $configuration['height'] ?? '',
                    $configuration['additionalParameters'],
                    $configuration,
                    $this->forceCreation
                );
                if ($tempFileInfo !== null) {
                    // Scaling
                    $command = '-geometry ' . $tempFileInfo->getWidth() . 'x' . $tempFileInfo->getHeight() . '!';
                    $imageOperations->mask(
                        $tempFileInfo->getRealPath(),
                        $temporaryFileName,
                        $maskImage->getForLocalProcessing(),
                        $maskBackgroundImage->getForLocalProcessing(),
                        $command,
                        $configuration
                    );
                    $maskBottomImage = $configuration['maskImages']['maskBottomImage'] ?? null;
                    $maskBottomImageMask = $configuration['maskImages']['maskBottomImageMask'] ?? null;
                    if ($maskBottomImage instanceof FileInterface && $maskBottomImageMask instanceof FileInterface) {
                        // Uses the temporary PNG file from the previous step and applies another mask
                        $imageOperations->mask(
                            $temporaryFileName,
                            $temporaryFileName,
                            $maskBottomImage->getForLocalProcessing(),
                            $maskBottomImageMask->getForLocalProcessing(),
                            $command,
                            $configuration
                        );
                    }
                }
                $result = $tempFileInfo;
            }
        }

        // check if the processing really generated a new file (scaled and/or cropped)
        if ($result !== null) {
            // The file processing yielded a different file extension than we anticipated. Most likely because
            // the processing service found out a file type needed to use fallback storage. In this case, we
            // append the actually received file extension to our file to be stored, which will also hint at
            // a failed conversion, like some-file.avif.jpg. Otherwise use the same file extension. This is
            // evaluated for persistence in @see LocalImageProcessor->processTaskWithLocalFile().
            $remapProcessedTargetFileExtension = ($targetFileExtension !== $result->getExtension())
                // Remap to correct image type extension.
                ? $result->getExtension()
                // No file extension remap required.
                : null;
            // @todo: realpath handling should be revisited, they may produce issues
            //        with open_basedir restrictions and/or lockRootPath.
            if ($result->getRealPath() !== realpath($originalFileName)) {
                $result = [
                    'width' => $result->getWidth(),
                    'height' => $result->getHeight(),
                    'filePath' => $result->getRealPath(),
                    'remapProcessedTargetFileExtension' => $remapProcessedTargetFileExtension,
                ];
            } else {
                // No file was generated
                $result = null;
            }
        }

        // If noScale option is applied, we need to reset the width and height to ensure the scaled values
        // are used for the generated image tag even if the image itself is not scaled. This is needed, as
        // the result is discarded due to the fact that the original image is used.
        // @see https://forge.typo3.org/issues/100972
        // Note: This should only happen if no image has been generated ($result === null).
        if ($result === null && ($configuration['noScale'] ?? false)) {
            $configuration = $task->getConfiguration();
            $localProcessedFile = $task->getSourceFile()->getForLocalProcessing(false);
            $imageDimensions = $imageOperations->getImageDimensions($localProcessedFile, true);
            $imageScaleInfo = ImageProcessingInstructions::fromCropScaleValues(
                $imageDimensions->getWidth(),
                $imageDimensions->getHeight(),
                $configuration['width'] ?? '',
                $configuration['height'] ?? '',
                $configuration
            );
            $targetFile->updateProperties([
                'width' => $imageScaleInfo->width,
                'height' => $imageScaleInfo->height,
            ]);
        }

        return $result;
    }

}
