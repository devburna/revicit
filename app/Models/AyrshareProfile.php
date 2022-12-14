<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AyrshareProfile extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'identity',
        'reference',
        'facebook',
        'fbg',
        'gmb',
        'instagram',
        'linkedin',
        'pinterest',
        'reddit',
        'telegram',
        'tiktok',
        'twitter',
        'youtube',
        'meta'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
        'company_id',
        'identity',
        'reference',
        'meta',
        'deleted_at',
        'updated_at',
        'created_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'facebook' => 'boolean',
        'fbg' => 'boolean',
        'gmb' => 'boolean',
        'instagram' => 'boolean',
        'linkedin' => 'boolean',
        'pinterest' => 'boolean',
        'reddit' => 'boolean',
        'telegram' => 'boolean',
        'tiktok' => 'boolean',
        'twitter' => 'boolean',
        'youtube' => 'boolean'
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    protected function meta(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => json_decode($value),
        );
    }
}
