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
        z-index: 999;
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
            <h4 class="text-center">Table Entry Update</h4>
            <form action="{{ route('entries.update', $editTableEntry->entry_id) }}" method="post">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3" style="pointer-events: none;">
                        <label for="table_id" class="form-label">Table</label>
                        <input type="text" name="table_id" class="form-control" id="table_id" value="{{ getTablename($editTableEntry->table_id) }}">
                    </div>
                    <div class="mb-2 multiselect-dropdown">
                        <input type="text" placeholder="Search..." class="search-box form-control" onclick="toggleDropdown()" onkeyup="filterOptions()">
                        <div class="dropdown-list" id="dropdown-list">
                            <label><b>Floors</b></label>
                            @foreach($floorData as $floor)
                            @if(in_array($floor->floor_name, $floors))
                            <label><input type="checkbox" class="check {{$floor->floor_id}}" id="floor" value="{{$floor->floor_name}}" checked>{{$floor->floor_name}}</label>
                            @else
                            <label><input type="checkbox" class="check {{$floor->floor_id}}" id="floor" value="{{$floor->floor_name}}">{{$floor->floor_name}}</label>
                            @endif
                            @endforeach

                            <label><b>Zonals</b></label>
                            @foreach($zonalData as $zonal)
                            @if(in_array($zonal->zonal_name, $zonals))
                            <label><input type="checkbox" class="check {{$zonal->zonal_id}}" id="zonal" value="{{$zonal->zonal_name}}" checked>{{$zonal->zonal_name}}</label>
                            @else
                            <label><input type="checkbox" class="check {{$zonal->zonal_id}}" id="zonal" value="{{$zonal->zonal_name}}">{{$zonal->zonal_name}}</label>
                            @endif
                            @endforeach

                            <label><b>Sensors</b></label>
                            @foreach($sensorData as $sensor)
                            @if(in_array($sensor->sensor_name, $sensors))
                            <label><input type="checkbox" class="check {{$sensor->sensor_id}}" id="sensor" value="{{$sensor->sensor_name}}" checked>{{$sensor->sensor_name}}</label>
                            @else
                            <label><input type="checkbox" class="check {{$sensor->sensor_id}}" id="sensor" value="{{$sensor->sensor_name}}">{{$sensor->sensor_name}}</label>
                            @endif
                            @endforeach
                        </div>

                    </div>
                    <div class="mb-3">
                        <textarea id="selectedValues" class="form-control" name="valueNames" id="valueNames" rows="3" readonly placeholder="Selected will appear here..." style="height: 70px !important;">{{$editTableEntry->floor_zonal_sensor_names}}</textarea>
                        <input type="hidden" name="values" id="selectedval" value="{{ $editTableEntry->floor_zonal_sensor_ids }}">
                    </div>
                    <div class="mb-2 multiselect-dropdown">
                        <label for="logic_no" class="form-label">Logic to Calculate Number</label>
                        <input type="text" placeholder="Search..." class="search-box-no form-control" onclick="toggleDropdown1()" onkeyup="filterOptions1()">
                        <div class="dropdown-list-no" id="dropdown-list-no">
                            <label><input type="checkbox" class="check-no" {{ in_array('red', $logic_no) ? 'checked' : '' }} value="red">Red</label>
                            <label><input type="checkbox" class="check-no" {{ in_array('green', $logic_no) ? 'checked' : '' }} value="green">Green</label>
                            <label><input type="checkbox" class="check-no" {{ in_array('blue', $logic_no) ? 'checked' : '' }} value="blue">Blue</label>
                            <label><input type="checkbox" class="check-no" {{ in_array('magenta', $logic_no) ? 'checked' : '' }} value="magenta">Magenta</label>
                            <label><input type="checkbox" class="check-no" {{ in_array('yellow', $logic_no) ? 'checked' : '' }} value="yellow">Yellow</label>
                            <label><input type="checkbox" class="check-no" {{ in_array('cyan', $logic_no) ? 'checked' : '' }} value="cyan">Cyan</label>
                            <label><input type="checkbox" class="check-no" {{ in_array('white', $logic_no) ? 'checked' : '' }} value="white">White</label>
                        </div>

                    </div>
                    <div class="mb-3">
                        <textarea id="selectedValuesNo" class="form-control" name="logic_no" id="logic_no" rows="3" readonly placeholder="Selected will appear here...">{{ $editTableEntry->logic_to_calculate_numbers }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="entry_name" class="form-label">Entry Name</label>
                        <input type="text" name="entry_name" class="form-control" id="entry_name" value="{{ $editTableEntry->entry_name }}" required>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="goBack()">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
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
        // document.getElementById("selectedValues").value = selected;
        // document.getElementById("selectedval").value = selectedIds;
    });
</script>
@endsection