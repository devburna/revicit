<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static CREDIT()
 * @method static static DEBIT()
 * @method static static OptionThree()
 */
final class PaymentType extends Enum
{
    const CREDIT = 'credit';
    const DEBIT = 'debit';
}
