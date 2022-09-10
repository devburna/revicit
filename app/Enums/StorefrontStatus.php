<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OPEN()
 * @method static static CLOSED()
 */
final class StorefrontStatus extends Enum
{
    const OPEN = 'open';
    const CLOSED = 'close';
}
