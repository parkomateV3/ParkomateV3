@extends('header')
@section('content')
<style>
    label {
        margin-bottom: 0 !important;
    }
</style>
<div class="container">
    <h2 class="text-center">EECS Data</h2>

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
                        <th scope="col">Device Name</th>
                        <th scope="col">Sensor Name</th>
                        <th scope="col">Type</th>
                        <th scope="col">From</th>
                        <th scope="col">To</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($eecsData as $sensor)
                    <tr>
                        <th scope="row">{{ $sensor->id }}</th>
                        <td>{{ getSitename($sensor->site_id) }}</td>
                        <td>{{ getDeviceName($sensor->device_id) }}({{getDeviceId($sensor->device_id)}})</td>
                        <td>{{ getEECSSensorNameNo($sensor->sensor_id) }}</td>
                        <td>{{ getTypeName($sensor->type) }}</td>
                        <td>{{ $sensor->from == 0 ? 'Entry' : getFloorname($sensor->from); }}</td>
                        <td>{{ $sensor->to == 0 ? 'Exit' : getFloorname($sensor->to); }}</td>
                        <td>
                            @if($can_edit == 1)
                            <form action="{{route('eecs.destroy', $sensor->id)}}" id="deleteForm-{{ $sensor->id }}" onsubmit="return confirmDelete(event, '{{ $sensor->id }}');" method="post">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                            </form>
                            <a href="{{ route('eecs.edit', $sensor->id) }}" class="btn btn-sm btn-outline-success ">Edit</a>
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
                <h5 class="modal-title" id="exampleModalLabel">EECS Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('eecs.store') }}" method="post">
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
                        <label for="device_id" class="form-label">Devices</label>
                        <select class="form-select" name="device_id" id="device_id" required>
                            <option value="" selected>Select Device</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="sensor_id" class="form-label">Sensors</label>
                        <select class="form-select" name="sensor_id" id="sensor_id" required>
                            <option value="" selected>Select Sensor</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="from" class="form-label">From</label>
                        <select class="form-select" name="from" id="from" required>
                            <option value="" selected>Select Floor</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="to" class="form-label">To</label>
                        <select class="form-select" name="to" id="to" required>
                            <option value="" selected>Select Floor</option>
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
                        $('#from').empty();
                        $('#from').append('<option value="" selected>Select Floor</option>');
                        $('#from').append('<option value="0">Entry</option>');
                        $.each(response.data, function(index, floor) {
                            // console.log(floor);    

                            $('#from').append('<option value="' + floor.floor_id + '">' + floor.floor_name + ' (' + floor.site_username + ')</option>');
                        });

                        $('#to').empty();
                        $('#to').append('<option value="" selected>Select Floor</option>');
                        $('#to').append('<option value="0">Exit</option>');
                        $.each(response.data, function(index, floor) {
                            // console.log(floor);    

                            $('#to').append('<option value="' + floor.floor_id + '">' + floor.floor_name + ' (' + floor.site_username + ')</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                    }
                });

                $.ajax({
                    url: '{{ url("eecsdevice") }}/' + selectedValue,
                    type: 'GET',
                    success: function(response) {

                        $('#device_id').empty();
                        $('#device_id').append('<option value="" selected>Select Device</option>');
                        $.each(response.data, function(index, device) {
                            // console.log(floor);

                            $('#device_id').append('<option value="' + device.id + '">' + device.device_name + ' (' + device.device_id + ')</option>');
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

        $('#device_id').on('change', function() {
            var selectedValue = $(this).val();

            if (selectedValue) {

                $.ajax({
                    url: '{{ url("eecssensor") }}/' + selectedValue,
                    type: 'GET',
                    success: function(response) {
                        console.log(response);

                        $('#sensor_id').empty();
                        $('#sensor_id').append('<option value="" selected>Select Sensor</option>');
                        $.each(response.data, function(index, sensor) {
                            // console.log(floor);

                            $('#sensor_id').append('<option value="' + sensor.id + '">' + sensor.sensor_name + ' (' + sensor.sensor_number + ')</option>');
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