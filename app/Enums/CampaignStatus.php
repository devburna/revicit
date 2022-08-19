<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static PENDING()
 * @method static static SUCCESS()
 * @method static static CANCELLED()
 */
final class CampaignStatus extends Enum
{
    const PENDING = 'pending';
    const SUCCESS = 'success';
    const CANCELLED = 'cancelled';
}
