<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrecheckEvent extends Model
{
    use HasFactory;

    protected $table = 'precheck_events';

    protected $fillable = [
        'campaign_id',
        'tenant_id',
        'project_id',
        'session_id',
        'request_id',
        'integration_mode',
        'ip',
        'host',
        'path',
        'query',
        'ua',
        'accept_language',
        'risk_score',
        'verdict',
        'reason_codes_json',
        'server_context_json',
        'client_context_json',
        'traffic_context_json',
    ];

    protected $casts = [
        'risk_score' => 'integer',
        'reason_codes_json' => 'array',
        'server_context_json' => 'array',
        'client_context_json' => 'array',
        'traffic_context_json' => 'array',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }
}
