<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static VIDEO()
 * @method static static IMAGE()
 * @method static static REELS()
 */
final class SocialPostType extends Enum
{
    const VIDEO = 'video';
    const IMAGE = 'image';
    const REELS = 'reels';
}
