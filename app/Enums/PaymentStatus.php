<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static PENDING()
 * @method static static SUCCESSFUL()
 * @method static static FAILED()
 */
final class PaymentStatus extends Enum
{
    const PENDING = 'pending';
    const SUCCESSFUL = 'successful';
    const FAILED = 'failed';
}
