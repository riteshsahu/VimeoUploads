<?php

namespace App\Http\Controllers;

use App\Video;
use Illuminate\Http\Request;
use App\Traits\UppyUploaderTrait;
use Illuminate\Support\Facades\Storage;
use Vimeo\Laravel\Facades\Vimeo;

class VideoController extends Controller
{
    use UppyUploaderTrait;

    public function store(Request $request)
    {
        // if ($request->file) {
        //     $video = Video::publishNewVideo($this->getMovedFilePath($request->file));

        //     return response([
        //         'path' => Storage::url($video->url)
        //     ], 201);
        // }

        // return response([], 400);
    }

    public function getUploadLink(Request $request)
    {

        // Create the Video
        $video_response =  Vimeo::request(
            '/me/videos',
            [
                'upload' => [
                    'approach' => 'tus',
                    "size" => $request->input('size'),
                    
                ],
                "name" => $request->input('name')
            ],
            'POST'
        );

        // dump("video response", $video_response);

        return response()->json($video_response);
    }

}
