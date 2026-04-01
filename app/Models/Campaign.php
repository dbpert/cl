<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'integration_mode',
        'target_mode',
        'target_redirect_url',
        'target_content_file',
        'bot_content_file',
        'all_countries',
        'is_active',
        'settings_json',
    ];

    protected $casts = [
        'all_countries' => 'boolean',
        'is_active' => 'boolean',
        'settings_json' => 'array',
    ];

    public function precheckEvents(): HasMany
    {
        return $this->hasMany(PrecheckEvent::class);
    }

    public function deviceEvents(): HasMany
    {
        return $this->hasMany(DeviceEvent::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(CampaignTag::class);
    }

    public function geoTargets(): HasMany
    {
        return $this->hasMany(CampaignGeoTarget::class);
    }
}
