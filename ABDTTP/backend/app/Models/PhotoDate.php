<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotoDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_time',
        'end_time',
        'capacity',
        'marker_id',
        'marker_name',
        'lat',
        'lng',
        'created_by',
        'created_by_username',
        'attendees',
    ];

    protected $casts = [
        'attendees' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'created_by' => 'integer',
        'marker_id' => 'integer',
        'capacity' => 'integer',
    ];
}
