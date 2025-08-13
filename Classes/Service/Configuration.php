<?php

declare(strict_types=1);

namespace Cylancer\CyWatermark\Service;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class Configuration implements SingletonInterface
{
    private static array $configuration = [];

    public static function get(?string $key = null): array|string|null
    {
        if (empty(self::$configuration)) {
            try {
                self::$configuration = GeneralUtility::makeInstance(ExtensionConfiguration::class)
                    ->get('cy_watermark');
            } catch (\Exception $e) {
                // Ignore
            }
        }

        if (!empty($key)) {
            if (isset(self::$configuration[$key])) {
                return trim((string) self::$configuration[$key]);
            }

            return null;
        }

        return self::$configuration;
    }
}
