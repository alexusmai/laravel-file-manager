<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'File Manager') }}</title>

    <!-- Styles -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.10/css/all.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css">
    <link href="{{ asset('vendor/file-manager/css/file-manager.css') }}" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12" style="height: 800px;">
            <div id="fm"></div>
        </div>
    </div>
</div>

<!-- File manager -->
<script src="{{ asset('vendor/file-manager/js/file-manager.js') }}"></script>
<script>
    // Helper function to get parameters from the query string.
    function getUrlParam(paramName) {
        const reParam = new RegExp('(?:[\?&]|&)' + paramName + '=([^&]+)', 'i');
        const match = window.location.search.match(reParam);

        return (match && match.length > 1) ? match[1] : null;
    }

    // Add callback to file manager
    fm.$store.commit('fm/setFileCallBack', function (fileUrl) {
      const funcNum = getUrlParam('CKEditorFuncNum');

      window.opener.CKEDITOR.tools.callFunction(funcNum, fileUrl);
      window.close();
    });
</script>
</body>
</html>

