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

enum SourceOption: int
{
    case NONE = 0;
    case CATEGORY = 1;
    case IMAGE = 2;

}