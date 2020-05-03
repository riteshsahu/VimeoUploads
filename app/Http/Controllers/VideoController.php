<?php

namespace App\Http\Controllers;

use App\Video;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class VideoController extends Controller
{
    public function store(Request $request)
    {
        // Create the Video
        $uploadMetadata = $request->header("upload-metadata");
        $uploadMetadata = explode(',', $uploadMetadata);
        $metaData = [];

        foreach ($uploadMetadata as $meta) {
            $data = explode(' ', $meta);
            $metaData[$data[0]] = $data[1];
        }

        $fileName = base64_decode($metaData['filename']);
        $fileId = $metaData['fileId'];

        // $video_response =  Vimeo::request(
        //     '/me/videos',
        //     [
        //         'upload' => [
        //             'approach' => 'tus',
        //             "size" => $request->header('upload-length'),

        //         ],
        //         "name" => substr($fileName, 0, 127)
        //     ],
        //     'POST'
        // );

        $client = new Client([
            "headers" => [
                'upload-length' => $request->header('upload-length'),
                "sec-fetch-dest" => "empty",
                "sec-fetch-mode" => "cors",
                "sec-fetch-site" => "cross-site",
                "tus-resumable" => "1.0.0"
            ]
        ]);

        $res = $client->post('https://master.tus.io/files/');
        $uploadLink = $res->getHeader("Location")[0];

        // try {
            $videoData = [
                'user_id' => auth()->user()->id,
                'video_id' => $fileId,
                "name" => substr($fileName, 0, 250),
                // "url" => $video_response["body"]["link"],
                "url" => "testing",
            ];

            $video = Video::create($videoData);

            return response([
                "data" => $video,
                // ])->header('Location', $video_response["body"]["upload"]["upload_link"]);
            ])->header('Location', $uploadLink);
        // } catch (\Throwable $th) {
        //     return response()->json([
        //         "data" => "Upload link not found.",
        //     ], 500);
        // }
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

        return $video->fresh();
    }
}
