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
    public function store(Request $request)
    {
        // Create the Video
        $uploadMetadata = $request->header("upload-metadata");
        $uploadMetadata = explode(',', $uploadMetadata);
        $parsedMetadata = [];

        foreach ($uploadMetadata as $metaData) {
            $data = explode(' ', $metaData);
            $data[1] = base64_decode($data[1]);
            $parsedMetadata[$data[0]] = $data[1];
        }

        $video_response =  Vimeo::request(
            '/me/videos',
            [
                'upload' => [
                    'approach' => 'tus',
                    "size" => $request->header('upload-length'),
                    
                ],
                "name" => substr($parsedMetadata['filename'], 0, 127)
            ],
            'POST'
        );

        try {
            $videoData = [
                'user_id' => auth()->user()->id,
                "name" => substr($parsedMetadata['filename'], 0, 250),
                "url" => $video_response["body"]["link"],
            ];

            $video = Video::create($videoData);

            return response([
                "data" => $video,
            ])->header('Location', $video_response["body"]["upload"]["upload_link"]);
        } catch (\Throwable $th) {
            return response()->json([
                "data" => "Upload link not found.",
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Video $video
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Video $video)
    {

        $video->update([
            'upload_success' => $request->upload_success,
        ]);

        return response()->json([
            "data" => "Video Uploaded"
        ]);
    }
}
