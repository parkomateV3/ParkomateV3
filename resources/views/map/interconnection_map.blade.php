<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title>Map</title>
</head>
<style>

</style>

<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="{{ asset('dashboard/assets/images/logo/logo-white-2.png') }}" alt="" width="80" height="60">
            </a>
        </div>
    </nav>
    <div class="container mt-5 mb-5 pb-5">

        <h4 class="text-center fw-bolder">You are in {{getSitename($site_id)}},<br> on {{getFloorname($floor_id)}}, near {{$location}},<br> your destination is at {{getFloorname($interFloorId)}},<br> please move to {{$interCoordinate}} for {{getFloorname($interFloorId)}}</h4>

        <div class="row" style="text-align: right;">
            <div class="col-12 col-md-4 mt-1">
                <img src="{{ asset('symbols/current_location.png') }}" class="img-fluid" alt="current_location" width="25">
                <span>Current Location</span>
            </div>
            <div class="col-12 col-md-4 mt-1">
                <img src="{{ asset('symbols/destination_location.png') }}" class="img-fluid" alt="current_location" width="25">
                <span>Destination Location</span>
            </div>
            <div class="col-12 col-md-4 mt-1">
                <img src="{{ asset('symbols/interconnect_location.png') }}" class="img-fluid" alt="current_location" width="25">
                <span>interconnect Location</span>
            </div>
        </div>

        <img src="{{ $processedImg }}" class="img-fluid mt-3" alt="current_location">

        <hr style="border: 1px solid black; width: 80%; opacity: 1; display: block; margin: 20px auto;">

        <img src="{{ $processedImg1 }}" class="img-fluid mt-3" alt="destination_location">

        <hr style="border: 2px solid black; opacity: 1; display: block; margin: 20px auto;">

    </div>

    @php $ad_image = getSiteAdImage($site_id); @endphp
    @if($ad_image != null || $ad_image != '')
    <footer class="bg-dark text-white text-center mt-auto w-100" style="bottom: 0;">
        <img src="{{ asset('logos/' . $ad_image) }}" class="" alt="footer-image" height="60" width="100%">
    </footer>
    @endif
</body>

<script>
</script>

</html>