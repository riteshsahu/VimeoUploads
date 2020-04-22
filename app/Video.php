<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = ['url', 'name', 'user_id',  'upload_success'];
}
