<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static MAIL()
 * @method static static SMS()
 * @method static static SOCIAL_NETWORK()
 */
final class CampaignType extends Enum
{
    const MAIL = 'mail';
    const SMS = 'sms';
    const SOCIAL_NETWORK = 'social-network';
}
