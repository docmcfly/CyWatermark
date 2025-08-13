<?php

declare(strict_types=1);

namespace Cylancer\CyWatermark\Service;

use Cylancer\CyWatermark\Domain\Model\Position;
use Cylancer\CyWatermark\Domain\Model\SourceOption;
use Exception;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;



final class WatermarkService
{

    use LoggerAwareTrait;

    private Connection $categoriesConnection;
    private FileRepository $fileRepository;

    public function __construct()
    {
        $this->categoriesConnection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_category_record_mm');
        $this->fileRepository = GeneralUtility::makeInstance(FileRepository::class);
    }

    /**
     * @param FileInterface|File $originalFile
     */
    public function process(ProcessedFile $originalProcessFile): void
    {

        $originalFile = $originalProcessFile->getOriginalFile();

        if ($originalFile->hasProperty("tx_cywatermark_watermark_source")) {
            switch (SourceOption::tryFrom($originalFile->getProperty("tx_cywatermark_watermark_source"))) {
                case SourceOption::NONE:
                    return;
                case SourceOption::CATEGORY:
                    $watermarkSettings = $this->get_category_watermark_settings($originalFile);
                    if (!empty($watermarkSettings)) {
                        foreach ($watermarkSettings as $watermarkSetting) {
                            $wm = $this->fileRepository->findByUid($watermarkSetting['tx_cywatermark_watermark_file']);
                            $this->addWatermark(
                                $originalProcessFile,
                                $wm,
                                Position::tryFrom($watermarkSetting["tx_cywatermark_watermark_position"]),
                                $watermarkSetting["tx_cywatermark_watermark_relative_size"]
                            );
                        }
                    }
                    return;
                case SourceOption::IMAGE:
                    $wmUid = $this->get_file_watermark_uid($originalFile);
                    if ($wmUid === false) {
                        return;
                    }
                    $wm = $this->fileRepository->findByUid($wmUid);
                    $this->addWatermark(
                        $originalProcessFile,
                        $wm,
                        Position::tryFrom($originalFile->getProperty("tx_cywatermark_watermark_position")),
                        !$originalFile->hasProperty("tx_cywatermark_watermark_relative_size") || $originalFile->getProperty("tx_cywatermark_watermark_relative_size") == null
                        ? null
                        : intval($originalFile->getProperty("tx_cywatermark_watermark_relative_size"))
                    );
                    return;

                default:
                    throw new Exception("tx_cywatermark_watermark_source = " . $originalFile . " is not supported");
            }
        }
    }

    public static function createImage(ProcessedFile|File $file): bool|\GdImage
    {
        if ($file->isImage()) {
            $filePath = $file->getStorage()->getConfiguration()['basePath'] . $file->getIdentifier();
            switch (strtolower($file->getExtension())) {
                case 'jpg':
                case 'jpeg':
                    return imagecreatefromjpeg($filePath);
                case 'png':
                    return imagecreatefrompng($filePath);
                case 'bmp':
                    return imagecreatefrombmp($filePath);
                case 'gif':
                    return imagecreatefromgif($filePath);
                case 'webp':
                    return imagecreatefromwebp($filePath);
            }
        }
        return false;
    }


    public static function saveImage($image, ProcessedFile $processedFile): bool|string
    {
        if ($processedFile->isImage()) {
            /** @var string $localProcessingFile */
            $localProcessingFile = $processedFile->getForLocalProcessing(true);
            $extension = strtolower($processedFile->getExtension());
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    imagejpeg($image, $localProcessingFile, 90);
                    return $localProcessingFile;
                case 'png':
                    imagepng($image, $localProcessingFile);
                    return $localProcessingFile;
                case 'bmp':
                    imagebmp($image, $localProcessingFile);
                    return $localProcessingFile;
                case 'gif':
                    imagegif($image, $localProcessingFile);
                    return $localProcessingFile;
                case 'webp':
                    imagewebp($image, $localProcessingFile);
                    return $localProcessingFile;
                default:
                    throw new Exception("Not supported image format: $extension");
            }
        }
        return false;
    }


    private function addWatermark(
        ProcessedFile $processedFile,
        File $watermarkFile,
        Position $position,
        ?int $relativeSize

    ): void {
        $padding = 10;

        $background = $this->createImage($processedFile);

        $watermark = $this->createImage($watermarkFile);
        imagealphablending($watermark, true);

        $bgWidth = imagesx($background);
        $bgHeight = imagesy($background);
        $target = imagecreatetruecolor($bgWidth, $bgHeight);
        imagealphablending($target, true);

        if ($position == Position::DIAGONAL_SLASH) { // rotate right
            $watermark = imagerotate($watermark, rad2deg(atan($bgHeight / $bgWidth)), imageColorAllocateAlpha($watermark, 0, 0, 0, 127));
        } else if ($position == Position::DIAGONAL_BACKSLASH) { // rotate left
            $watermark = imagerotate($watermark, -rad2deg(atan($bgHeight / $bgWidth)), imageColorAllocateAlpha($watermark, 0, 0, 0, 127));
        }
        $wmWidth = imagesx($watermark);
        $wmHeight = imagesy($watermark);
        if ($relativeSize != null && $relativeSize > 0) {

            $bgXPlace = ($bgWidth - (2 * $padding)) * $relativeSize / 100;
            $bgYPlace = ($bgHeight - (2 * $padding)) * $relativeSize / 100;

            $scaleX = $bgXPlace / $wmWidth;
            $scaleY = $bgYPlace / $wmHeight;

            $scale = min($scaleX, $scaleY);
            $wmWidth = (int) round($wmWidth * $scale);
            $wmHeight = (int) round(num: $wmHeight * $scale);
            $watermark = imagescale($watermark, $wmWidth, $wmHeight);
        }



        switch ($position) {
            case Position::TOP_LEFT: // top left
                $posX = $padding;
                $posY = $padding;
                break;
            case Position::TOP_RIGHT:// top right
                $posX = $bgWidth - $wmWidth - $padding;
                $posY = $padding;
                break;
            case Position::BOTTOM_LEFT: // bottom left
                $posX = $padding;
                $posY = $bgHeight - $wmHeight - $padding;
                break;
            case Position::BOTTOM_RIGHT: // bottom right
                $posX = $bgWidth - $wmWidth - $padding;
                $posY = $bgHeight - $wmHeight - $padding;
                break;
            case Position::CENTER: // center
            case Position::DIAGONAL_SLASH: // diagonal slash
            case Position::DIAGONAL_BACKSLASH: // diagonal backslash
                $posX = (($bgWidth - $wmWidth) / 2) - $padding;
                $posY = (($bgHeight - $wmHeight) / 2) - $padding;
                break;
        }


        imagecopy($target, $background, 0, 0, 0, 0, $bgWidth, $bgHeight);
        imagecopy($target, $watermark, intval($posX), intval($posY), 0, 0, $wmWidth, $wmHeight);

        $processedFile->updateWithLocalFile($this->saveImage($target, $processedFile));

        imagedestroy($target);
        imagedestroy($background);
        imagedestroy($watermark);
    }


    public function get_category_watermark_settings(File $file): bool|array
    {
        $categoryUids = $this->categoriesConnection->createQueryBuilder()
            ->select('uid_local')
            ->from('sys_category_record_mm')
            ->where('tablenames = :table')
            ->andWhere('uid_foreign = :uid')
            ->setParameters([
                'table' => 'sys_file_metadata',
                'uid' => $file->getMetaData()->get()['uid'],
            ])
            ->executeQuery()
            ->fetchFirstColumn();


        if (empty($categoryUids)) {
            return [];
        }
        return $this->categoriesConnection->createQueryBuilder()
            ->select('sc.uid AS uid', 'sfr.uid_local AS tx_cywatermark_watermark_file', 'sc.tx_cywatermark_watermark_relative_size AS tx_cywatermark_watermark_relative_size', 'sc.tx_cywatermark_watermark_position AS tx_cywatermark_watermark_position')
            ->from('sys_category', 'sc')
            ->join('sc', 'sys_file_reference', 'sfr', 'sc.uid = sfr.uid_foreign AND sfr.tablenames = :table AND sfr.fieldname = :fieldname')
            ->where('sc.uid IN (' . implode(',', $categoryUids) . ')')
            ->setParameters([
                'table' => 'sys_category',
                'fieldname' => 'tx_cywatermark_watermark_file',
            ])
            ->executeQuery()
            ->fetchAllAssociative();
    }

    public function get_file_watermark_uid(File $source): bool|int
    {
        return $this->categoriesConnection->createQueryBuilder()
            ->select('uid_local AS uid')
            ->from('sys_file_reference')
            ->where('uid_foreign = :source_file_uid')
            ->andWhere('tablenames = :table')
            ->andWhere('fieldname = :fieldname')
            ->setParameters([
                'table' => 'sys_file_metadata',
                'fieldname' => 'tx_cywatermark_watermark_file',
                'source_file_uid' => $source->getMetaData()->get()['uid'],
            ])
            ->executeQuery()
            ->fetchOne();
    }

    public static function isSupportedMimeType(string $mimeType): bool
    {
        $supportedMimeTypes = (string) Configuration::get('mime_types');
        if (!empty($supportedMimeTypes)) {
            return \in_array(\strtolower($mimeType), \explode(',', \strtolower($supportedMimeTypes)), true);
        }

        return false;
    }

}