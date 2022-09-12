<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static CHECKBOX()
 * @method static static RADIO()
 * @method static static TEXTAREA()
 */
final class StorefrontProductOptionType extends Enum
{
    const CHECKBOX = 'checkbox';
    const RADIO = 'radio';
    const TEXTAREA = 'textarea';
}
