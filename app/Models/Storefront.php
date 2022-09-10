<?php

namespace App\Models;

use App\Enums\StorefrontStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Storefront extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'tagline',
        'domain',
        'description',
        'logo_url',
        'currency',
        'welcome_message',
        'success_message',
        'delivery_address',
        'delivery_note',
        'redirect_after_payment_url',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'company_id',
        'company'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => StorefrontStatus::class,
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    protected function siteUrl(): Attribute
    {
        return Attribute::make(
            set: fn ($value, $attributes) => config('app.url') . "/{$value}",
        );
    }
}
