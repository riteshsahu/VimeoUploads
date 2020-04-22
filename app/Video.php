<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = ['video_id', 'url', 'name', 'user_id',  'upload_success'];

    protected $primaryKey = 'video_id';
}
