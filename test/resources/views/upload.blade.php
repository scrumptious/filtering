<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
   
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

</head>
<body>
<div class="container">
    {{-- flash messages --}}
    @if(session()->has('success'))
        <div class="alert alert-success">
            {{ session()->get('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger mt-2">
            {{ session()->get('error') }} 
        </div>
    @endif
    @if (session()->has('result'))
        <div class="alert alert-info mt-2">
            {{ session()->get('result') }} 
        </div>
    @endif

    <form method="post" action="{{ route('file.upload') }}" enctype="multipart/form-data">
        @csrf
        <label for="title" class="col-sm-4 col-form-label text-md-right">Upload file</label>
        <input type="file" name="file" id="file">

        {{-- <div class="form-group row ">
            <label for="title" class="col-sm-4 col-form-label text-md-right">Upload file</label>
            <div class="col-md-6">
              <div id="file" class="dropzone"></div>
            </div>
          </div> --}}

        <button type="submit" class="btn btn-primary">
            {{ __('Upload') }}
        </button>
    </form>
</div>
@push('scripts')
<script>
    $(document).ready(function () {
        Dropzone.autoDiscover = false;
        $("#dZUpload").dropzone({
            url: "hn_SimpeFileUploader.ashx",
            addRemoveLinks: true,
            success: function (file, response) {
                var imgName = response;
                file.previewElement.classList.add("dz-success");
                console.log("Successfully uploaded :" + imgName);
            },
            error: function (file, response) {
                file.previewElement.classList.add("dz-error");
            }
        });
    });
</script>
@endpush
</body>
</html>