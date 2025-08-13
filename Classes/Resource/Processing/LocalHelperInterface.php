<?php
/*
 * .
 */

namespace Cylancer\CyWatermark\Resource\Processing;


interface LocalHelperInterface
{
    /**
     * Returns the name of the helper. 
     * 
     * @return string
     */
    public function getName(): string;

    /**
     * This method actually does the processing of files locally
     *
     * takes the original file (on remote storages this will be fetched from the remote server)
     * does the IM magic on the local server by creating a temporary typo3temp/ file
     * copies the typo3temp/ file to the processing folder of the target storage
     * removes the typo3temp/ file
     *
     * The returned array has the following structure:
     *   width => 100
     *   height => 200
     *   filePath => /some/path
     *
     * If filePath isn't set but width and height are the original file is used as ProcessedFile
     * with the returned width and height. This is for example useful for SVG images.
     *
     * @param \TYPO3\CMS\Core\Resource\Processing\TaskInterface $task
     * @return array|null
     */
    public function process(\TYPO3\CMS\Core\Resource\Processing\TaskInterface $task);

    /**
     * Does the heavy lifting prescribed in processTask()
     * except that the processing can be performed on any given local image
     */
    public function processWithLocalFile(\TYPO3\CMS\Core\Resource\Processing\TaskInterface $task, string $localFile): ?array;
}