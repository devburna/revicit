<?php

namespace App\Models;

use App\Enums\StorefrontStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Storefront extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

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

    /**
     * Route notifications for the Vonage channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string
     */
    public function routeNotificationForVonage($notification)
    {
        return $this->company->phone;
    }

    /**
     * Route notifications for the mail channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return array|string
     */
    public function routeNotificationForMail($notification)
    {
        // Return email address only...
        return $this->company->email;

        // Return email address and name...
        return [$this->company->email => $this->company->name];
    }

    protected function siteUrl(): Attribute
    {
        return Attribute::make(
            set: fn ($value, $attributes) => config('app.url') . "/{$value}",
        );
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(StorefrontProduct::class, 'storefront_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(StorefrontCustomer::class, 'storefront_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(StorefrontOrder::class, 'storefront_id');
    }
}
