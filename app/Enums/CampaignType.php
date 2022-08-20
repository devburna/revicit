<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static MAIL()
 * @method static static SMS()
 * @method static static MAIL_SMS()
 * @method static static SOCIAL_MEDIA()
 * @method static static MAIL_SMS()
 */
final class CampaignType extends Enum
{
    const MAIL = 'mail';
    const SMS = 'sms';
    const MAIL_SMS = 'mail-sms';
    const SOCIAL_MEDIA = 'social-media';
}
