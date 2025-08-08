@extends('header')
@section('content')
<style>
    label {
        margin-bottom: 0 !important;
    }
</style>
<div class="container">
    <h2 class="text-center">Sensor Data</h2>

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
                        <th scope="col">Floor Name</th>
                        <th scope="col">Zonal Name</th>
                        <th scope="col">Sensor Name</th>
                        <th scope="col">Sensor No</th>
                        <th scope="col">Sensor Range</th>
                        <th scope="col">Color Occupied</th>
                        <th scope="col">Color Available</th>
                        <th scope="col">Role</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sensorData as $sensor)
                    @php $color = $sensor->barrier_id == null ? 'white' : '#ffff66'; @endphp
                    <tr style="background-color: <?php echo $color; ?>;">
                        <th scope="row">{{ $sensor->sensor_id }}</th>
                        <td>{{ getSitename($sensor->site_id) }}</td>
                        <td>{{ getFloorname($sensor->floor_id) }}</td>
                        <td>{{ getZonalname($sensor->zonal_id) }}</td>
                        <td>{{ $sensor->sensor_name }}</td>
                        <td>{{ $sensor->sensor_unique_no }}</td>
                        <td>{{ $sensor->sensor_range }}</td>
                        <td>{{ $sensor->color_occupied }}</td>
                        <td>{{ $sensor->color_available }}</td>
                        <td>{{ $sensor->role }}</td>
                        <td>
                            @if($can_edit == 1)
                            <form action="{{route('sensor.destroy', $sensor->sensor_id)}}" id="deleteForm-{{ $sensor->sensor_id }}" onsubmit="return confirmDelete(event, '{{ $sensor->sensor_id }}');" method="post">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                            </form>
                            <a href="{{ route('sensor.edit', $sensor->sensor_id) }}" class="btn btn-sm btn-outline-success ">Edit</a>
                            @endif
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
                <h5 class="modal-title" id="exampleModalLabel">Sensor Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('sensor.store') }}" method="post">
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
                        <label for="sensor_no" class="form-label">Sensor Number</label>
                        <input type="text" name="sensor_no" class="form-control" id="sensor_no" value="{{ old('sensor_no') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="sensor_name" class="form-label">Sensor Name</label>
                        <input type="text" name="sensor_name" class="form-control" id="sensor_name" value="{{ old('sensor_name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="near_piller" class="form-label">Select near piller</label>
                        <select class="form-select" name="near_piller" id="near_piller">
                            <option value="" selected>Select Piller</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="sensor_range" class="form-label">Sensor Range</label>
                        <input type="number" name="sensor_range" class="form-control" id="sensor_range" value="180" required>
                    </div>
                    <div class="mb-3">
                        <label for="color_occupied" class="form-label">Color Occupied</label>
                        <select class="form-select" name="color_occupied" id="color_occupied" required>
                            <option value="">Select Color</option>
                            <option value="red" selected>Red</option>
                            <option value="green">Green</option>
                            <option value="blue">Blue</option>
                            <option value="magenta">Magenta</option>
                            <option value="yellow">Yellow</option>
                            <option value="cyan">Cyan</option>
                            <option value="white">White</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="color_available" class="form-label">Color Available</label>
                        <select class="form-select" name="color_available" id="color_available" required>
                            <option value="">Select Color</option>
                            <option value="red">Red</option>
                            <option value="green" selected>Green</option>
                            <option value="blue">Blue</option>
                            <option value="magenta">Magenta</option>
                            <option value="yellow">Yellow</option>
                            <option value="cyan">Cyan</option>
                            <option value="white">White</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" name="role" id="role" required>
                            <option value="">Select Role</option>
                            <option value="loop" selected>Loop</option>
                            <option value="single">Single</option>
                            <option value="off">Off</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="barrier_id" class="form-label">Barrier ID</label>
                        <input type="text" name="barrier_id" class="form-control" id="barrier_id">
                    </div>
                    <div class="mb-3">
                        <label for="barrier_color" class="form-label">Barrier Color</label>
                        <select class="form-select" name="barrier_color" id="barrier_color">
                            <option value="">Select Barrier Color</option>
                            <option value="red">Red</option>
                            <option value="green">Green</option>
                            <option value="blue">Blue</option>
                            <option value="magenta">Magenta</option>
                            <option value="yellow" selected>Yellow</option>
                            <option value="cyan">Cyan</option>
                            <option value="white">White</option>
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

                $.ajax({
                    url: '{{ url("get_piller_data") }}/' + selectedValue,
                    type: 'GET',
                    success: function(response) {

                        $('#near_piller').empty();
                        $('#near_piller').append('<option value="" selected>Select Piller</option>');
                        $.each(response, function(index, piller) {
                            // console.log(floor);    

                            $('#near_piller').append('<option value="' + piller + '">' + piller + '</option>');
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
    });
</script>

@endsection