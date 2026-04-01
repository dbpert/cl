<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'session_id',
        'agua',
        'referrer',
        'cl_referrer',
        'referal',
        'fingerprint_json',
    ];

    protected $casts = [
        'fingerprint_json' => 'array',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
