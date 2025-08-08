@extends('header')
@section('content')
<style>
    label {
        margin-bottom: 0 !important;
    }
</style>
<div class="container">
    <h2 class="text-center">EECS Sensor Data</h2>

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
                        <th scope="col">Device ID</th>
                        <th scope="col">Sensor No</th>
                        <th scope="col">Sensor Name</th>
                        <th scope="col">Detection Type</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($eecsData as $sensor)
                    <tr>
                        <th scope="row">{{ $sensor->id }}</th>
                        <td>{{ getSitename($sensor->site_id) }}</td>
                        <td>{{ getDeviceId($sensor->device_id) }}({{getDeviceName($sensor->device_id)}})</td>
                        <td>{{ $sensor->sensor_number }}</td>
                        <td>{{ $sensor->sensor_name }}</td>
                        <td>{{ getTypeName($sensor->detection_type) }}</td>
                        <td>
                            @if($can_edit == 1)
                            <form action="{{route('eecssensor.destroy', $sensor->id)}}" id="deleteForm-{{ $sensor->id }}" onsubmit="return confirmDelete(event, '{{ $sensor->id }}');" method="post">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                            </form>
                            <a href="{{ route('eecssensor.edit', $sensor->id) }}" class="btn btn-sm btn-outline-success ">Edit</a>
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
                <h5 class="modal-title" id="exampleModalLabel">EECS Sensor Add</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('eecssensor.store') }}" method="post">
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
                        <label for="sensor_number" class="form-label">Sensor Number</label>
                        <input type="text" name="sensor_number" class="form-control" id="sensor_number" value="{{ old('sensor_number') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="sensor_name" class="form-label">Sensor Name</label>
                        <input type="text" name="sensor_name" class="form-control" id="sensor_name" value="{{ old('sensor_name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="detection_type" class="form-label">Detection Type</label>
                        <select class="form-select" name="detection_type" id="detection_type" required>
                            <option value="" selected>Select Type</option>
                            @foreach($types as $type)
                            <option value="{{ $type->id }}">{{ $type->type }}</option>
                            @endforeach
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
                    url: '{{ url("eecsdevice") }}/' + selectedValue,
                    type: 'GET',
                    success: function(response) {
                        $('#device_id').empty();
                        $('#device_id').append('<option value="" selected>Select Device</option>');
                        $.each(response.data, function(index, device) {
                            // console.log(device);

                            $('#device_id').append('<option value="' + device.id + '">' + device.device_id + '</option>');
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