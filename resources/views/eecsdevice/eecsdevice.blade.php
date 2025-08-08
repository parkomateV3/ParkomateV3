@extends('header')
@section('content')
<style>
    label {
        margin-bottom: 0 !important;
    }

    /* Style for the dropdown */
    .multiselect-dropdown {
        width: 100%;
        position: relative;
    }

    /* Search box inside the dropdown */
    .search-box {
        width: 100%;
        padding: 5px;
        border: 1px solid #ccc;
        border-radius: 5px
    }

    /* Style the dropdown items */
    .dropdown-list {
        border: 1px solid #ccc;
        max-height: 150px;
        overflow-y: auto;
        display: none;
        position: absolute;
        width: 100%;
        background-color: white;
        z-index: 99999;
    }

    .dropdown-list label {
        display: block;
        padding: 5px;
        cursor: pointer;
    }

    .dropdown-list label:hover {
        background-color: #f0f0f0;
    }

    .dropdown-list input {
        margin-right: 5px;
    }

    .dropdown-list.active {
        display: block;
    }
</style>
<div class="container">
    <h2 class="text-center">EECS Device Data</h2>

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
                        <th scope="col">Device Name</th>
                        <th scope="col">Detection List</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($eecsData as $eecs)
                    <tr>
                        <th scope="row">{{ $eecs->id }}</th>
                        <td>{{ getSitename($eecs->site_id) }}</td>
                        <td>{{ $eecs->device_id }}</td>
                        <td>{{ $eecs->device_name }}</td>
                        @php $detectionList = explode(',', $eecs->detection_list); @endphp
                        <td>
                            @foreach($detectionList as $type)
                            <span class="badge bg-secondary">{{ getTypeName($type) }}</span>
                            @endforeach
                        </td>
                        <td>
                            @if($can_edit == 1)
                            <form action="{{route('eecsdevice.destroy', $eecs->id)}}" id="deleteForm-{{ $eecs->id }}" onsubmit="return confirmDelete(event, '{{ $eecs->id }}');" method="post">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                            </form>
                            <a href="{{ route('eecsdevice.edit', $eecs->id) }}" class="btn btn-sm btn-outline-success ">Edit</a>
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
                <h5 class="modal-title" id="exampleModalLabel">EECS Device Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('eecsdevice.store') }}" method="post">
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
                        <label for="device_id" class="form-label">Device ID</label>
                        <input type="text" name="device_id" class="form-control" id="device_id" value="{{ old('device_id') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="device_name" class="form-label">Device Name</label>
                        <input type="text" name="device_name" class="form-control" id="device_name" value="{{ old('device_name') }}" required>
                    </div>
                    <div class="mb-2 multiselect-dropdown">
                        <label for="logic_no" class="form-label">Select Detection Types</label>
                        <input type="text" placeholder="Search..." class="search-box form-control" onclick="toggleDropdown()" onkeyup="filterOptions()">
                        <div class="dropdown-list" id="dropdown-list">
                            @foreach($types as $type)
                            <label><input type="checkbox" class="check" value="{{ $type->id }}">{{ $type->type }}</label>
                            @endforeach
                        </div>
                        <input type="hidden" name="detection_list" id="selectedval">
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

    // Toggle the display of the dropdown list
    function toggleDropdown() {
        document.getElementById("dropdown-list").classList.toggle("active");
    }

    // Filter options based on search
    function filterOptions() {
        const searchBox = document.querySelector('.search-box').value.toLowerCase();
        const labels = document.querySelectorAll('.dropdown-list label');

        labels.forEach(label => {
            const text = label.innerText.toLowerCase();
            if (text.includes(searchBox)) {
                label.style.display = "block";
            } else {
                label.style.display = "none";
            }
        });
    }

    // Use event delegation for dynamically added checkboxes
    $(document).on('change', '.check', function() {
        const checkboxes = document.querySelectorAll('#dropdown-list label input[type="checkbox"]');

        let selected = [];


        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {

                selected.push(checkbox.value);
            }
        });

        document.getElementById("selectedval").value = selected;
    });
</script>

@endsection