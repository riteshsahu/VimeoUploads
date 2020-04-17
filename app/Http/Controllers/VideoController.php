<?php

namespace App\Http\Controllers;

use App\Video;
use Illuminate\Http\Request;
use App\Traits\UppyUploaderTrait;
use Illuminate\Support\Facades\Storage;
use Vimeo\Laravel\Facades\Vimeo;
use Illuminate\Support\Str;

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
        $uploadMetadata = $request->header("upload-metadata");
        $uploadMetadata = explode(',', $uploadMetadata);
        $parsedMetadata = [];

        foreach ($uploadMetadata as $metaData) {
            $data = explode(' ', $metaData);
            $parsedMetadata[$data[0]] = $data[1];
        }

        $video_response =  Vimeo::request(
            '/me/videos',
            [
                'upload' => [
                    'approach' => 'tus',
                    "size" => $request->header('upload-length'),
                    
                ],
                "name" => $parsedMetadata['filename']
            ],
            'POST'
        );

        // return response()->json($video_response["body"]["upload"]["upload_link"]);
        return response()->json()->header('Location', $video_response["body"]["upload"]["upload_link"]);
    }
}
