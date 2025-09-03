@extends('header')
@section('content')

<div class="container">
    <h2 class="text-center">Processor Data</h2>
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
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($processorData as $processor)
                    <tr>
                        <th scope="row">{{ $processor->id }}</th>
                        <td>{{getSitename($processor->site_id)}}</td>
                        <td>{{ $processor->processor_id }}</td>
                        <td>
                            @if($can_edit == 1)
                            <form action="{{route('processor.destroy', $processor->id)}}" id="deleteForm-{{ $processor->id }}" onsubmit="return confirmDelete(event, '{{ $processor->id }}');" method="post">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                            </form>
                            <a href="{{ route('processor.edit', $processor->id) }}" class="btn btn-sm btn-outline-success ">Edit</a>
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
                <h5 class="modal-title" id="exampleModalLabel">Add Processor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('processor.store') }}" method="post">
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
                        <label for="processor_id" class="form-label">Processor ID</label>
                        <input type="text" name="processor_id" class="form-control" id="processor_id" value="{{ old('processor_id') }}" required>
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
</script>
@endsection