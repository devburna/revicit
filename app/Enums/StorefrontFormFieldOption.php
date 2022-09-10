<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static DISABLED()
 * @method static static REQUIRED()
 * @method static static OPTIONAL()
 */
final class StorefrontFormFieldOption extends Enum
{
    const DISABLED = 'disabled';
    const REQUIRED = 'required';
    const OPTIONAL = 'optional';
}
