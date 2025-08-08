<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <title>Map</title>
</head>


<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="{{ asset('dashboard/assets/images/logo/logo-white-2.png') }}" alt="" width="80" height="60">
            </a>
        </div>
    </nav>
    <div class="container mt-5 mb-5 pb-5">

        <h4 class="text-center fw-bolder">You are in {{getSitename($site_id)}},<br> on {{getFloorname($floor_id)}}, near {{$location}}</h4>

        <div class="row" style="text-align:right;">
            <div class="col-12 col-md-4 mt-1">
                <img src="{{ asset('symbols/current_location.png') }}" class="img-fluid" alt="current_location" width="25">
                <span>Current Location</span>
            </div>
            @if($flag == 0)
            <div class="col-12 col-md-4 mt-1">
                <img src="{{ asset('symbols/destination_location.png') }}" class="img-fluid" alt="current_location" width="25">
                <span>Destination Location</span>
            </div>
            @endif
        </div>

        <img src="{{ $processedImg }}" class="img-fluid mt-3" alt="current_location">

        <hr style="border: 2px solid black; width: 80%; opacity: 1; display: block; margin: 20px auto;">


        @if($flag)
        <h4 class="text-center fw-bolder">Select the destination where you want to go from your current location</h4>
        <form action="{{route('get_map')}}" method="post" class="text-center mt-3" id="pillerForm">
            @csrf
            <input type="hidden" name="site_id" id="site_id" value="{{$site_id}}">
            <input type="hidden" name="floor_id" value="{{$floor_id}}">
            <input type="hidden" name="location" value="{{$location}}">
            <select name="categories" id="categories" class="form-select rounded-pill fw-bold bg-warning d-block mx-auto" style="width: 60%;" aria-label="Default select example" required>
                <option value="">Select Category</option>
                @foreach ($categories as $category)
                @if($category == $destinationLocation)
                <option value="{{$category}}" selected>{{$category}}</option>
                @else
                <option value="{{$category}}">{{$category}}</option>
                @endif
                @endforeach
            </select>
            <br>
            <select name="piller_name" id="piller_name" onchange="submitForm()" class="form-select rounded-pill fw-bold bg-warning d-block mx-auto" style="width: 60%;" aria-label="Default select example" required>
                <option value="">Select Destination Location</option>
            </select>
            <!-- <button type="submit" class="btn btn-success rounded-pill mt-3">Find</button> -->
        </form>
        @endif
    </div>

    <!-- Footer -->
    @php $ad_image = getSiteAdImage($site_id); @endphp
    @if($ad_image != null || $ad_image != '')
    <footer class="bg-dark text-white text-center mt-auto w-100" style="bottom: 0;">
        <img src="{{ asset('logos/' . $ad_image) }}" class="" alt="footer-image" height="60" width="100%">
    </footer>
    @endif
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        submitForm = function() {
            document.getElementById('pillerForm').submit();
            // alert(document.getElementById('piller_name').value);
        }

        $(document).ready(function() {
            $('#categories').on('change', function() {
                var selectedValue = $(this).val();
                var site_id = $('#site_id').val();
                const baseUrl = window.location.origin + "/get_category_data/" + site_id + "/" + selectedValue;
                // alert(baseUrl);
                if (selectedValue) {
                    $.ajax({
                        url: baseUrl,
                        type: 'GET',
                        success: function(response) {
                            console.log(response);
                            $('#piller_name').empty();
                            $('#piller_name').append('<option value="" selected>Select Destination Location</option>');
                            $.each(response, function(index, location) {
                                // console.log(floor);    

                                $('#piller_name').append('<option value="' + location + '">' + location + '</option>');
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log(xhr.responseText);
                        }
                    });
                } else {
                    $('#piller_name').empty();
                    $('#piller_name').append('<option value="" selected>Select Destination Location</option>');
                    alert("Please select a site.");
                }

            });
        })
    </script>
    <style>
        .fixed-bottom-img {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            /* Ensures it stays above other elements */
        }
    </style>
</body>

</html>