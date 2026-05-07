<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FaceWidget extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'mode',
        'widget_auth_type', // 'register' or 'login'
        'position',
        'theme_color',
        'allowed_domains',
        'allowed_pages',
        'api_limit',
        'api_hits',
        'is_active',
        'welcome_title',
        'welcome_message',
        'button_text',
        'button_color',
        'show_start_button', // true = manual button, false = auto-start on load
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'show_start_button' => 'boolean',
        'api_hits'          => 'integer',
        'api_limit'         => 'integer',
        'widget_auth_type'  => 'string',
    ];
}