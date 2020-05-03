<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = ['url', 'video_id', 'name', 'user_id',  'upload_success'];
}
