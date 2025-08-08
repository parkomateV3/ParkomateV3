@extends('header')
@section('content')
<style>
    label {
        margin: 0 !important;
    }
</style>

<!-- https://demo.mobiscroll.com/jquery/select/multiple-lines# -->
<div class="container">
    <h2 class="text-center">Display Info</h2>

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
    <button class="btn btn-primary float-right" data-bs-toggle="modal" data-bs-target="#displayModal">Add Data</button>
    <br><br>
    <div class="row m-auto">
        <div class="col-md-12 m-auto">

            <table id="userTable" class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Site Name</th>
                        <th scope="col">Display Number</th>
                        <th scope="col">Display Location On Site</th>
                        <th scope="col">Intensity</th>
                        <th scope="col">No of Panels</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($displayData as $display)
                    <tr>
                        <th scope="row">{{ $display->display_id }}</th>
                        <td>{{ getSitename($display->site_id) }}</td>
                        <td>{{ $display->display_unique_no }}</td>
                        <td>{{ $display->location_of_the_display_on_site }}</td>
                        <td>{{ $display->intensity }}</td>
                        <td>{{ $display->panels }}</td>
                        <td>
                            @if($can_edit == 1)
                            <form action="{{route('display.destroy', $display->display_id)}}" id="deleteForm-{{ $display->display_id }}" onsubmit="return confirmDelete(event, '{{ $display->display_id }}');" method="post">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                            </form>
                            <a href="{{ route('display.edit', $display->display_id) }}" class="btn btn-sm btn-outline-success ">Edit</a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>


</div>

<div class="modal fade" id="displayModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Display Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('display.store') }}" method="post">
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
                        <label for="display_no" class="form-label">Display Number</label>
                        <input type="text" name="display_no" class="form-control" id="display_no" value="{{ old('display_no') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="display_location" class="form-label">Location of the Display On Site</label>
                        <input type="text" name="display_location" class="form-control" id="display_location" value="{{ old('location_of_the_display_on_site') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="intensity" class="form-label">Intensity</label>
                        <select class="form-select" name="intensity" id="intensity" required>
                            <option value="">Select Intensity</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="panels" class="form-label">No of Panels</label>
                        <input type="number" name="panels" class="form-control" id="panels" value="{{ old('panels') }}" required>
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
    function confirmDelete(event, displayId) {
        event.preventDefault(); // Prevent the default form submission

        const form = document.getElementById(`deleteForm-${displayId}`);

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

    
</script>

@endsection