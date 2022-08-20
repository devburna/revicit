<?php

namespace App\Models;

use App\Enums\CampaignStatus;
use App\Enums\CampaignType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Campaign extends Model
{
    use HasFactory, SoftDeletes, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'title',
        'type',
        'template',
        'scheduled_for',
        'meta',
        'status',
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
        'type' => CampaignType::class,
        'status' => CampaignStatus::class,
    ];

    /**
     * Route notifications for the Vonage channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string
     */
    public function routeNotificationForVonage($notification)
    {
        return $this->phone;
    }

    /**
     * Route notifications for the mail channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return array|string
     */
    public function routeNotificationForMail($notification)
    {
        // Return email address and name...
        return [$this->email => $this->name];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(CampaignLog::class, 'campaign_id');
    }
}
