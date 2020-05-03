@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Vimeo Upload Video</div>

                <div class="card-body">
                    <uppy-uploader></uppy-uploader>
                    <!-- <vimeo-uppy-uploader></vimeo-uppy-uploader> -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection