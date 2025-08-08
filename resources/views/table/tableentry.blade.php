@extends('header')
@section('content')

<style>
    /* Style for the dropdown */
    .multiselect-dropdown {
        width: 100%;
        position: relative;
    }

    /* Search box inside the dropdown */
    .search-box,
    .search-box-no {
        width: 100%;
        padding: 5px;
        border: 1px solid #ccc;
        border-radius: 5px
    }

    /* Style the dropdown items */
    .dropdown-list,
    .dropdown-list-no {
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

    .dropdown-list-no label {
        display: block;
        padding: 5px;
        cursor: pointer;
    }

    .dropdown-list label:hover {
        background-color: #f0f0f0;
    }

    .dropdown-list-no label:hover {
        background-color: #f0f0f0;
    }

    .dropdown-list input {
        margin-right: 5px;
    }

    .dropdown-list-no input {
        margin-right: 5px;
    }

    .dropdown-list.active {
        display: block;
    }

    .dropdown-list-no.active {
        display: block;
    }

    label {
        margin: 0 !important;
    }
</style>

<div class="container">
    <h2 class="text-center">Table Entries</h2>
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
    <button class="btn btn-primary float-right" data-bs-toggle="modal" data-bs-target="#tableModal">Add Data</button>
    <br><br>
    <div class="row m-auto">
        <div class="col-md-12 m-auto">

            <table id="userTable" class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Site Name</th>
                        <th scope="col">Table Name</th>
                        <th scope="col">Entry Name</th>
                        <th scope="col">Floor Zonal Sensor Names</th>
                        <th scope="col">Logic to Calculate Numbers</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tableEntry as $entry)
                    <tr>
                        <th scope="row">{{ $entry->table_id }}</th>
                        <td>{{getSitename($entry->site_id)}}</td>
                        <td>{{ getTableName($entry->table_id) }}</td>
                        <td>{{ $entry->entry_name }}</td>
                        <td>{{ $entry->floor_zonal_sensor_names }}</td>
                        <td>{{ $entry->logic_to_calculate_numbers }}</td>
                        <td>
                            @if($can_edit == 1)
                            <form action="{{route('entries.destroy', $entry->entry_id)}}" id="deleteForm-{{ $entry->entry_id }}" onsubmit="return confirmDelete(event, '{{ $entry->entry_id }}');" method="post">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                            </form>
                            <a href="{{ route('entries.edit', $entry->entry_id) }}" class="btn btn-sm btn-outline-success ">Edit</a>
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
                <h5 class="modal-title" id="exampleModalLabel">Add Entry</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('entries.store') }}" method="post">
                @csrf
                <div class="modal-body">

                    <div class="mb-3">
                        <label for="table_id" class="form-label">Tables</label>
                        <select class="form-select" name="table_id" id="table_id" required>
                            <option value="" selected>Select Table</option>
                            @foreach($tableData as $table)
                            <option value="{{$table->table_id}}">{{$table->table_name}} ({{ getSitename($table->site_id) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2 multiselect-dropdown">
                        <input type="text" placeholder="Search..." class="search-box form-control" onclick="toggleDropdown()" onkeyup="filterOptions()">
                        <div class="dropdown-list" id="dropdown-list">

                        </div>

                    </div>
                    <div class="mb-3">
                        <textarea id="selectedValues" class="form-control" name="valueNames" id="valueNames" rows="3" readonly placeholder="Selected will appear here..."></textarea>
                        <input type="hidden" name="values" id="selectedval">
                    </div>
                    <div class="mb-2 multiselect-dropdown">
                        <label for="logic_no" class="form-label">Logic to Calculate Number</label>
                        <input type="text" placeholder="Search..." class="search-box-no form-control" onclick="toggleDropdown1()" onkeyup="filterOptions1()">
                        <div class="dropdown-list-no" id="dropdown-list-no">
                            <label><input type="checkbox" class="check-no" value="red">Red</label>
                            <label><input type="checkbox" class="check-no" value="green">Green</label>
                            <label><input type="checkbox" class="check-no" value="blue">Blue</label>
                            <label><input type="checkbox" class="check-no" value="magenta">Magenta</label>
                            <label><input type="checkbox" class="check-no" value="yellow">Yellow</label>
                            <label><input type="checkbox" class="check-no" value="cyan">Cyan</label>
                            <label><input type="checkbox" class="check-no" value="white">White</label>
                        </div>

                    </div>
                    <div class="mb-3">
                        <textarea id="selectedValuesNo" class="form-control" name="logic_no" id="logic_no" rows="3" readonly placeholder="Selected will appear here..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="entry_name" class="form-label">Entry Name</label>
                        <input type="text" name="entry_name" class="form-control" id="entry_name" value="{{ old('entry_name') }}" required>
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

    // Toggle the display of the dropdown list
    function toggleDropdown() {
        document.getElementById("dropdown-list").classList.toggle("active");
    }

    function toggleDropdown1() {
        document.getElementById("dropdown-list-no").classList.toggle("active");
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

    function filterOptions1() {
        const searchBox = document.querySelector('.search-box-no').value.toLowerCase();
        const labels = document.querySelectorAll('.dropdown-list-no label');

        labels.forEach(label => {
            const text = label.innerText.toLowerCase();
            if (text.includes(searchBox)) {
                label.style.display = "block";
            } else {
                label.style.display = "none";
            }
        });
    }

    // Update the textarea with selected values
    // function updateTextarea() {
    //     const checkboxes = document.querySelectorAll('.dropdown-list input[type="checkbox"]');
    //     const selected = [];

    //     checkboxes.forEach(checkbox => {
    //         if (checkbox.checked) {
    //             selected.push(checkbox.value);
    //         }
    //     });

    //     document.getElementById("selectedValues").value = selected.join(', ');
    // }

    // Use event delegation for dynamically added checkboxes
    $(document).on('change', '.check', function() {
        const checkboxes = document.querySelectorAll('#dropdown-list label input[type="checkbox"]');
        let selected = [];
        let selectedIds = [];
        const floor = [];
        const zonal = [];
        const sensor = [];

        const floorIds = [];
        const zonalIds = [];
        const sensorIds = [];


        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                // selected.push(checkbox.value);
                let selectedVal = checkbox.className;
                let selectedvalue = selectedVal.substring(6);
                // console.log(selectedvalue);
                if (checkbox.id == 'floor') {
                    floor.push(checkbox.value);
                    floorIds.push(selectedvalue);
                }
                if (checkbox.id == 'zonal') {
                    zonal.push(checkbox.value);
                    zonalIds.push(selectedvalue);
                }
                if (checkbox.id == 'sensor') {
                    sensor.push(checkbox.value);
                    sensorIds.push(selectedvalue);
                }
            }
        });

        selected.push(floor);
        selected.push(zonal);
        selected.push(sensor);

        selectedIds.push(floorIds);
        selectedIds.push(zonalIds);
        selectedIds.push(sensorIds);

        selected = '{"Floor":"' + floor + '","Zonals":"' + zonal + '","Sensors":"' + sensor + '"}';
        selectedIds = '{"Floor":"' + floorIds + '","Zonals":"' + zonalIds + '","Sensors":"' + sensorIds + '"}';

        // document.getElementById("selectedValues").value = selected.join(', ');
        document.getElementById("selectedValues").value = selected;
        document.getElementById("selectedval").value = selectedIds;
    });

    $(document).on('change', '.check-no', function() {
        const checkboxes = document.querySelectorAll('#dropdown-list-no label input[type="checkbox"]');
        let selected = [];


        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                selected.push(checkbox.value);
            }
        });

        document.getElementById("selectedValuesNo").value = selected.join(',');
    });


    $(document).ready(function() {
        $('#table_id').on('change', function() {
            var selectedValue = $(this).val();

            if (selectedValue) {
                $.ajax({
                    url: '{{ url("entries") }}/' + selectedValue,
                    type: 'GET',
                    success: function(response) {
                        // console.log(response);
                        $('.dropdown-list').empty();
                        // $('#dropdown-list').append('<option value="" selected>Select Floor</option>');
                        $('.dropdown-list').append('<label><b>Floors</b></label>');
                        $.each(response.floor, function(index, floor) {
                            // console.log(floor.floor_id);    
                            $('.dropdown-list').append('<label><input type="checkbox" class="check ' + floor.floor_id + '" id="floor" value="' + floor.floor_name + '">' + floor.floor_name + '</label>');
                        });

                        $('.dropdown-list').append('<label><b>Zonals</b></label>');
                        $.each(response.zonal, function(index, zonal) {
                            // console.log(zonal.zonal_id);    
                            $('.dropdown-list').append('<label><input type="checkbox" class="check ' + zonal.zonal_id + '" id="zonal" value="' + zonal.zonal_name + '">' + zonal.zonal_name + '</label>');
                        });

                        $('.dropdown-list').append('<label><b>Sensors</b></label>');
                        $.each(response.sensor, function(index, sensor) {
                            // console.log(sensor.sensor_id);    
                            $('.dropdown-list').append('<label><input type="checkbox" class="check ' + sensor.sensor_id + '" id="sensor" value="' + sensor.sensor_name + '">' + sensor.sensor_name + '</label>');
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