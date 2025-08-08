@extends('header')
@section('content')

<style>
    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }

    input:checked+.slider {
        background-color: #2196F3;
    }

    input:focus+.slider {
        box-shadow: 0 0 1px #2196F3;
    }

    input:checked+.slider:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
    }

    /* Rounded sliders */
    .slider.round {
        border-radius: 34px;
    }

    .slider.round:before {
        border-radius: 50%;
    }
</style>

<div class="container">
    <h2 class="text-center">Barrier Data</h2>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
    </div>
    @endif

    <!-- Display Success Message -->
    @if (session('message'))
    <div class="alert alert-success">
        {{ session('message') }}
    </div>
    @endif
    <button class="btn btn-primary float-right" data-bs-toggle="modal" data-bs-target="#sensorModal">Add Data</button>
    <br><br>
    <div class="row m-auto">
        <div class="col-md-12 m-auto">

            <table id="userTable" class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Site Name</th>
                        <th scope="col">Zonal Name</th>
                        <th scope="col">Sensor Name</th>
                        <th scope="col">Barrier No</th>
                        <th scope="col">Is Blocked</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reservationData as $reservation)
                    <tr>
                        <th scope="row">{{ $reservation->id }}</th>
                        <td>{{ getSitename($reservation->site_id) }}</td>
                        <td>{{ getZonalNameNo($reservation->zonal_id) }}</td>
                        <td>{{ getSensorNameNo($reservation->sensor_id) }}</td>
                        <td>{{ $reservation->barrier_unique_no }}</td>
                        <!-- <td>{{ $reservation->is_blocked }}</td> -->
                        <td>
                            <label class="switch">
                                <input type="checkbox" value="{{ $reservation->id }}" class="is_blocked" {{ $reservation->is_blocked == 1 ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="sensorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Barrier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('reservation.store') }}" method="post">
                @csrf
                <div class="modal-body">

                    <div class="mb-3">
                        <label for="site_id" class="form-label">Sites</label>
                        <select class="form-select" name="site_id" id="site_id" required>
                            <option value="" selected>Select Site</option>
                            @foreach($siteData as $site)
                            <option value="{{$site->site_id}}">{{$site->site_name}} ({{ $site->site_username }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="floor_id" class="form-label">Floors</label>
                        <select class="form-select" name="floor_id" id="floor_id" required>
                            <option value="" selected>Select Floor</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="zonal_id" class="form-label">Zonals</label>
                        <select class="form-select" name="zonal_id" id="zonal_id" required>
                            <option value="" selected>Select Zonal</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="sensor_id" class="form-label">Sensors</label>
                        <select class="form-select" name="sensor_id" id="sensor_id" required>
                            <option value="" selected>Select Sensor</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="is_blocked" class="form-label">Is Blocked</label>
                        <select class="form-select" name="is_blocked" id="is_blocked" required>
                            <option value="">Select</option>
                            <option value="1">Yes</option>
                            <option value="0" selected>No</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Function to handle delete confirmation
    function confirmDelete(event, sensorId) {
        event.preventDefault(); // Prevent the default form submission

        const form = document.getElementById(`deleteForm-${sensorId}`);

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit(); // Submit the correct form
            }
        });

        return false; // Return false to ensure the form does not submit immediately
    }

    $(document).ready(function() {

        $('#site_id').on('change', function() {
            var selectedValue = $(this).val();

            if (selectedValue) {
                $.ajax({
                    url: '{{ url("floor") }}/' + selectedValue,
                    type: 'GET',
                    success: function(response) {
                        $('#floor_id').empty();
                        $('#floor_id').append('<option value="" selected>Select Floor</option>');
                        $.each(response.data, function(index, floor) {
                            // console.log(floor);    

                            $('#floor_id').append('<option value="' + floor.floor_id + '">' + floor.floor_name + ' (' + floor.site_username + ')</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                    }
                });
            } else {
                alert("Please select a site.");
            }

        });

        $('#floor_id').on('change', function() {
            var selectedValue = $(this).val();

            if (selectedValue) {
                $.ajax({
                    url: '{{ url("zonal") }}/' + selectedValue,
                    type: 'GET',
                    success: function(response) {
                        $('#zonal_id').empty();
                        $('#zonal_id').append('<option value="" selected>Select Zonal</option>');
                        $.each(response.data, function(index, zonal) {
                            // console.log(floor);    

                            $('#zonal_id').append('<option value="' + zonal.zonal_id + '">' + zonal.zonal_name + ' (' + zonal.floor_name + ')</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                    }
                });
            } else {
                alert("Please select a site.");
            }

        });

        $('#zonal_id').on('change', function() {
            var selectedValue = $(this).val();

            if (selectedValue) {
                $.ajax({
                    url: '{{ url("sensor") }}/' + selectedValue,
                    type: 'GET',
                    success: function(response) {
                        $('#sensor_id').empty();
                        $('#sensor_id').append('<option value="" selected>Select Sensor</option>');
                        $.each(response.data, function(index, sensor) {
                            // console.log(floor);    

                            $('#sensor_id').append('<option value="' + sensor.sensor_id + '">' + sensor.sensor_unique_no + ' (' + sensor.sensor_name + ')</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                    }
                });
            } else {
                alert("Please select a site.");
            }

        });

        $('.is_blocked').on('change', function() {
            var id = $(this).val();

            $.ajax({
                url: '{{ url("barrier-status-update") }}/' + id,
                type: 'GET',
                data: {
                    is_blocked: $(this).is(':checked') ? 1 : 0
                },
                success: function(response) {
                    console.log(response);
                    if (response.s == 1) {
                        Swal.fire({
                            title: response.data,
                            icon: 'success',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Ok'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // form.submit(); // Submit the correct form
                            }
                        });
                    }
                    if (response.s == 0) {
                        Swal.fire({
                            title: response.data,
                            icon: 'warning',
                            showCancelButton: false,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'Ok'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // form.submit(); // Submit the correct form
                            }
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.log(xhr.responseText);
                }
            });

        });
    });
</script>

@endsection