<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static DRAFT()
 * @method static static PUBLISHED()
 * @method static static SCHEDULED()
 */
final class CampaignStatus extends Enum
{
    const DRAFT = 'draft';
    const PUBLISHED = 'published';
    const SCHEDULED = 'scheduled';
}
