<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static PUBLISHED()
 * @method static static DRAFT()
 */
final class StorefrontProductStatus extends Enum
{
    const PUBLISHED = 'published';
    const DRAFT = 'draft';
}
