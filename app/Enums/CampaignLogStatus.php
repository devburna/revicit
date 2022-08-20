<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static SENT()
 * @method static static FAILED()
 */
final class CampaignLogStatus extends Enum
{
    const SENT = 'sent';
    const FAILED = 'failed';
}
