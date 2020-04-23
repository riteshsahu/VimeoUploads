<?php

namespace App\Http\Controllers;

use App\Video;
use Illuminate\Http\Request;
use Vimeo\Laravel\Facades\Vimeo;

class VideoController extends Controller
{
    public function store(Request $request)
    {
        try {

            // Create the Video
            $video_response =  Vimeo::request(
                '/me/videos',
                [
                    'upload' => [
                        'approach' => 'tus',
                        "size" => $request->input('size'),

                    ],
                    "name" => substr($request->input('filename'), 0, 127)
                ],
                'POST'
            );

            $videoData = [
                'user_id' => auth()->user()->id,
                "name" => substr($request->input('filename'), 0, 250),
                "url" => $video_response["body"]["link"],
            ];

            $video = Video::create($videoData);

            return response([
                "data" => $video,
            ])->header('Location', $video_response["body"]["upload"]["upload_link"]);
        } catch (\Throwable $th) {
            return response()->json([
                "data" => "Error while uploading the video.",
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
            'upload_success' => $request->input('upload_success'),
        ]);

        return $video->fresh();
    }
}
