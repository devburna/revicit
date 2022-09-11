<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static RECEIVED()
 * @method static static PROCESSING()
 * @method static static OUT_FOR_DELIVERY()
 * @method static static DELIVERED()
 * @method static static CANCELLED()
 */
final class StorefrontOrderStatus extends Enum
{
    const RECEIVED = 'received';
    const PROCESSING = 'processing';
    const OUT_FOR_DELIVERY = 'out-for-delivery';
    const DELIVERED = 'delivered';
    const CANCELLED = 'cancelled';
}
