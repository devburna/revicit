<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static FACEBOOK()
 * @method static static INSTAGRAM()
 * @method static static LINKEDIN()
 * @method static static TELEGRAM()
 * @method static static TIKTOK()
 * @method static static TWITTER()
 */
final class SocialPlatforms extends Enum
{
    const FACEBOOK = 'facebook';
    const INSTAGRAM = 'instagram';
    const LINKEDIN = 'linkedin';
    const TELEGRAM = 'telegram';
    const TIKTOK = 'tiktok';
    const TWITTER = 'twitter';
}
