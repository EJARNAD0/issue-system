<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    protected $fillable = [
        'title',
        'description',
        'priority',
        'category',
        'status',
        'summary',
        'suggested_action',
        'is_escalated',
    ];

    protected $casts = [
        'is_escalated' => 'boolean',
    ];
}
