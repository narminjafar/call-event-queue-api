<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CallEventLog extends Model
{
    protected $table = 'call_event_logs';

    public $timestamps = false;


    protected $fillable = [
        'call_id',
        'event_type',
        'payload',
        'created_time',
    ];


    protected $casts = [
        'payload' => 'array',
        'created_time' => 'datetime',
    ];
}
