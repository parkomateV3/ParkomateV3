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
<!-- https://demo.mobiscroll.com/jquery/select/multiple-lines# -->
<div class="container">
    <h2 class="text-center">Display Data</h2>

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

            <table id="userTable" class="table table-hover table-bordered table-responsive">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Site Name</th>
                        <th scope="col">Display Location (NO)</th>
                        <th scope="col">Coordinates</th>
                        <th scope="col">Floor Zonal Sensor IDs</th>
                        <th scope="col">Logic Calculate Number</th>
                        <th scope="col">Display Format</th>
                        <th scope="col">Font</th>
                        <th scope="col">Font Size</th>
                        <th scope="col">Color</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($displayData as $display)
                    <tr>
                        <th scope="row">{{ $display->data_id }}</th>
                        <td>{{ getSitename($display->site_id) }}</td>
                        <td>{{ getDisplayLocation($display->display_id) }}</td>
                        <td>{{ $display->coordinates }}</td>
                        <td>{{ $display->floor_zonal_sensor_names }}</td>
                        <td>{{ $display->logic_calculate_number }}</td>
                        <td>{{ $display->display_format }}</td>
                        <td>{{ $display->font }}</td>
                        <td>{{ $display->font_size }}</td>
                        <td>{{ $display->color }}</td>
                        <td>
                            @if($can_edit == 1)
                            <form action="{{route('displaydata.destroy', $display->data_id)}}" id="deleteForm-{{ $display->data_id }}" onsubmit="return confirmDelete(event, '{{ $display->data_id }}');" method="post">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                            </form>
                            <a href="{{ route('displaydata.edit', $display->data_id) }}" class="btn btn-sm btn-outline-success ">Edit</a>
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
                <h5 class="modal-title" id="exampleModalLabel">Display Data Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('displaydata.store') }}" method="post">
                @csrf
                <div class="modal-body">

                    <div class="mb-3">
                        <label for="display_id" class="form-label">Displays</label>
                        <select class="form-select" name="display_id" id="display_id" required>
                            <option value="" selected>Select Display</option>
                            @foreach($displaysData as $displays)
                            <option value="{{$displays->display_id}}">{{$displays->location_of_the_display_on_site}} ({{ $displays->display_unique_no }})</option>
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
                        <label for="display_format" class="form-label">Display Format</label>
                        <input type="text" name="display_format" class="form-control" id="display_format" value="{{ old('display_format') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="math" class="form-label">Math Logic</label>
                        <input type="text" name="math" class="form-control" id="math" value="{{ old('math') }}" placeholder="Enter Math Logic">
                    </div>
                    <div class="mb-3">
                        <label for="coordinates" class="form-label">Coordinates</label>
                        <input type="text" name="coordinates" class="form-control" id="coordinates" value="{{ old('coordinates') }}" placeholder=" x, y " required>
                    </div>
                    <div class="mb-3">
                        <label for="font" class="form-label">Font</label>
                        <select class="form-select" name="font" id="font" required>
                            <option value="">Select Font</option>
                            <option value="F1">Font 1</option>
                            <option value="F2">Font 2</option>
                            <option value="F3">Font 3</option>
                            <option value="F4">Font 4</option>
                            <option value="F5">Font 5</option>
                            <option value="F6">Font 6</option>
                            <option value="F7">Font 7</option>
                            <option value="F8">Font 8</option>
                            <option value="F9">Font 9</option>
                            <option value="F10">Font 10</option>
                            <option value="F11">Font 11</option>
                            <option value="F12">Font 12</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="font_size" class="form-label">Font Size</label>
                        <select class="form-select" name="font_size" id="font_size" required>
                            <option value="">Select Font Size</option>
                            <option value="S0">Size 0</option>
                            <option value="S1">Size 1</option>
                            <option value="S2">Size 2</option>
                            <option value="S3">Size 3</option>
                            <option value="S4">Size 4</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="color" class="form-label">Color</label>
                        <input type="text" class="form-control" name="color" placeholder="(R,G,B)" id="color" required>

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
        $('#display_id').on('change', function() {
            var selectedValue = $(this).val();

            if (selectedValue) {
                $.ajax({
                    url: '{{ url("displaydata") }}/' + selectedValue,
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