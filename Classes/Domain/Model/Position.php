<?php

namespace Cylancer\CyWatermark\Domain\Model;

/**
 * This file is part of the "cyWatermark" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 *
 */

enum Position: int
{
    case TOP_LEFT = 0;
    case TOP_RIGHT = 1;
    case BOTTOM_LEFT = 2;
    case BOTTOM_RIGHT = 3;
    case CENTER = 4;
    case DIAGONAL_SLASH = 5;
    case DIAGONAL_BACKSLASH = 6;
}