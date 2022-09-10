<?php

namespace App\Models;

use App\Enums\StorefrontProductQuantity;
use App\Enums\StorefrontProductStatus;
use App\Enums\StorefrontProductType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StorefrontProduct extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'storefront_id',
        'name',
        'slug',
        'description',
        'tags',
        'regular_price',
        'sale_price',
        'quantity',
        'stock_keeping_unit',
        'stock_quantity',
        'item_unit',
        'type',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'storefront_id',
        'storefront'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => StorefrontProductQuantity::class,
        'type' => StorefrontProductType::class,
        'status' => StorefrontProductStatus::class,
    ];

    protected function tags(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => explode(',', $value),
        );
    }

    public function storefront(): BelongsTo
    {
        return $this->belongsTo(Storefront::class, 'storefront_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(StorefrontProductImage::class, 'storefront_product_id');
    }
}
