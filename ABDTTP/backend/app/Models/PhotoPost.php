<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotoPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'created_by_username',
        'marker_id',
        'marker_name',
        'lat',
        'lng',
        'image_url',
        'caption',
    ];

    protected $casts = [
        'created_by' => 'integer',
        'marker_id' => 'integer',
    ];

    public function likes()
    {
        return $this->hasMany(PhotoPostLike::class, 'post_id');
    }

    public function bookmarks()
    {
        return $this->hasMany(PhotoPostBookmark::class, 'post_id');
    }

    public function marker()
    {
        return $this->belongsTo(Marker::class, 'marker_id');
    }
}
