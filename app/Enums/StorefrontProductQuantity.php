<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static LIMITED()
 * @method static static UNLIMITED()
 */
final class StorefrontProductQuantity extends Enum
{
    const LIMITED = 'limited';
    const UNLIMITED = 'unlimited';
}
