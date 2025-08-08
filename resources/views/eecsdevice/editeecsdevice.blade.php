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
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
    </div>
    @endif
    <div class="row">
        <div class="col-md-6 m-auto">
            <h4 class="text-center">EECS Device Update</h4>
            <form action="{{ route('eecsdevice.update', $editDevice->id) }}" method="post">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3" style="pointer-events: none;">
                        <label for="display_id" class="form-label">Site Name</label>
                        <input type="text" name="display_id" class="form-control" id="display_id" value="{{ getSitename($editDevice->site_id) }}">
                    </div>
                    <div class="mb-3" style="pointer-events: none;">
                        <label for="device_id" class="form-label">Device ID</label>
                        <input type="text" name="device_id" class="form-control" id="device_id" value="{{ $editDevice->device_id }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="device_name" class="form-label">Device Name</label>
                        <input type="text" name="device_name" class="form-control" id="device_name" value="{{ $editDevice->device_name }}" required>
                    </div>
                    <div class="mb-2 multiselect-dropdown">
                        <label for="logic_no" class="form-label">Logic to Calculate Number</label>
                        <input type="text" placeholder="Search..." class="search-box form-control" onclick="toggleDropdown()" onkeyup="filterOptions()">
                        <div class="dropdown-list" id="dropdown-list">
                            @foreach($types as $type)
                            <label><input type="checkbox" class="check" {{ in_array($type->id, $detectionList) ? 'checked' : '' }} value="{{ $type->id }}">{{ $type->type }}</label>
                            @endforeach
                        </div>
                        <input type="hidden" name="detection_list" id="selectedval" value="{{$editDevice->detection_list}}">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="goBack()">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function goBack() {
        window.history.back(); // or use window.history.go(-1);
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