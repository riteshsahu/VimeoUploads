<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = ['url'];

    public static function publishNewVideo($url)
    {
        return static::create([
            'url' => $url
        ]);
    }
}
