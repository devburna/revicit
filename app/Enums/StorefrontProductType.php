<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static SIMPLE()
 * @method static static DIGITAL()
 */
final class StorefrontProductType extends Enum
{
    const SIMPLE = 'simple';
    const DIGITAL = 'digital';
}
