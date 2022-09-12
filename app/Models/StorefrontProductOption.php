<?php

namespace App\Models;

use App\Enums\StorefrontProductOptionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StorefrontProductOption extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'storefront_product_id',
        'label',
        'description',
        'type',
        'min',
        'max',
        'required'

    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'storefront_product_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => StorefrontProductOptionType::class
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(StorefrontProduct::class, 'storefront_product_id');
    }
}
