@extends('header')
@section('content')
<style>
    label {
        margin: 0 !important;
    }
</style>

<!-- https://demo.mobiscroll.com/jquery/select/multiple-lines# -->
<div class="container">
    <h2 class="text-center">Detection Type Data</h2>

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
    <br><br>
    <div class="row m-auto">
        <div class="col-md-12 m-auto">

            <table id="userTable" class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Site Name</th>
                        <th scope="col">Detection Type</th>
                        <th scope="col">Name</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detectionData as $detection)
                    <tr>
                        <th scope="row">{{ $detection->id }}</th>
                        <td>{{ getSitename($detection->site_id) }}</td>
                        <td>{{ getTypeName($detection->type_id) }}</td>
                        <td>{{ $detection->name }}</td>
                        <td>
                            @if($can_edit == 1)

                            <a href="{{ route('editdetectiontype', $detection->id) }}" class="btn btn-sm btn-outline-success ">Edit</a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
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