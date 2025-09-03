@extends('header')
@section('content')

<div class="container">
    <h2 class="text-center">Camera Data</h2>
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <!-- Display Success Message -->
    @if (session('message'))
    <div class="alert alert-success">
        {{ session('message') }}
    </div>
    @endif
    <button class="btn btn-primary float-right" data-bs-toggle="modal" data-bs-target="#tableModal">Add Data</button>
    <br><br>
    <div class="row m-auto">
        <div class="col-md-12 m-auto">

            <table id="userTable" class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Site Name</th>
                        <th scope="col">Processor ID</th>
                        <th scope="col">IP Address</th>
                        <th scope="col">Camera Access Link</th>
                        <th scope="col">Image</th>
                        <th scope="col">Camera Identifier</th>
                        <th scope="col">Parking Slot Details</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cameraData as $camera)
                    <tr>
                        <th scope="row">{{ $camera->id }}</th>
                        <td>{{getSitename($camera->site_id)}}</td>
                        <td>{{ $camera->processor_id }}</td>
                        <td>{{ $camera->local_ip_address }}</td>
                        <td>{{ $camera->camera_access_link }}</td>
                        <td>{{ $camera->image }}</td>
                        <td>{{ $camera->camera_identifier }}</td>
                        <td>{{ $camera->parking_slot_details }}</td>
                        <td>
                            @if($can_edit == 1)
                            <form action="{{route('camerainfo.destroy', $camera->id)}}" id="deleteForm-{{ $camera->id }}" onsubmit="return confirmDelete(event, '{{ $camera->id }}');" method="post">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                            </form>
                            <a href="{{ route('camerainfo.edit', $camera->id) }}" class="btn btn-sm btn-outline-success ">Edit</a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="tableModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add Camera</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('camerainfo.store') }}" method="post">
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
                        <label for="processor_id" class="form-label">Processors</label>
                        <select class="form-select" name="processor_id" id="processor_id" required>
                            <option value="" selected>Select Processor</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="local_ip_address" class="form-label">Camera IP Address</label>
                        <input type="text" name="local_ip_address" class="form-control" id="local_ip_address" value="{{ old('local_ip_address') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="camera_access_link" class="form-label">Access Link</label>
                        <input type="text" name="camera_access_link" class="form-control" id="camera_access_link" value="{{ old('camera_access_link') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="camera_identifier" class="form-label">Camera Identifier</label>
                        <input type="text" name="camera_identifier" class="form-control" id="camera_identifier" value="{{ old('camera_identifier') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="parking_slot_details" class="form-label">Parking Slot Details</label>
                        <textarea name="parking_slot_details" class="form-control textarea_height" id="parking_slot_details" required>{{ old('parking_slot_details') }}</textarea>
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

<script>
    // Function to handle delete confirmation
    function confirmDelete(event, floorId) {
        event.preventDefault(); // Prevent the default form submission

        const form = document.getElementById(`deleteForm-${floorId}`);

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
                    url: '{{ url("processor") }}/' + selectedValue,
                    type: 'GET',
                    success: function(response) {
                        $('#processor_id').empty();
                        $('#processor_id').append('<option value="" selected>Select Processor</option>');
                        $.each(response.data, function(index, processor) {
                            // console.log(processor);    

                            $('#processor_id').append('<option value="' + processor.id + '">' + processor.processor_id + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                    }
                });
            } else {
                alert("Please select a processor.");
            }

        });
    });
</script>
@endsection