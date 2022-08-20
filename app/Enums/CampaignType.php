<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static EMAIL()
 * @method static static SMS()
 * @method static static SOCIAL_POST()
 * @method static static EMAIL_SMS()
 */
final class CampaignType extends Enum
{
    const EMAIL = 'email';
    const SMS = 'sms';
    const SOCIAL_POST = 'social-post';
}
