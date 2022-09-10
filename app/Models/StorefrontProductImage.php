<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorefrontProductImage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'storefront_product_id',
        'name',
        'image_url',
        'meta'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'storefront_product_id',
        'meta'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        //
    ];

    protected function meta(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => json_decode($value),
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(StorefrontProduct::class, 'storefront_product_id');
    }
}
