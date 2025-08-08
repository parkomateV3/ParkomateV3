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
            <h4 class="text-center">Display Data Update</h4>
            <form action="{{ route('displaydata.update', $editDisplay->data_id) }}" method="post">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3" style="pointer-events: none;">
                        <label for="display_id" class="form-label">Display</label>
                        <input type="text" name="display_id" class="form-control" id="display_id" value="{{ $editDisplay->display_id }} ({{ getSitename($editDisplay->site_id) }})">
                        <input type="hidden" name="site_id" value="{{ $editDisplay->site_id }}">
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
                        <textarea id="selectedValues" class="form-control" name="valueNames" id="valueNames" rows="3" readonly placeholder="Selected will appear here..." style="height: 70px !important;">{{$editDisplay->floor_zonal_sensor_names}}</textarea>
                        <input type="hidden" name="values" id="selectedval" value="{{ $editDisplay->floor_zonal_sensor_ids }}">
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
                        <textarea id="selectedValuesNo" class="form-control" name="logic_no" rows="3" readonly placeholder="Selected will appear here...">{{ $editDisplay->logic_calculate_number }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="display_format" class="form-label">Display Format</label>
                        <input type="text" name="display_format" class="form-control" id="display_format" value="{{ $editDisplay->display_format }}">
                    </div>
                    <div class="mb-3">
                        <label for="math" class="form-label">Math Logic</label>
                        <input type="text" name="math" class="form-control" id="math" value="{{ $editDisplay->math }}">
                    </div>
                    <div class="mb-3">
                        <label for="coordinates" class="form-label">Coordinates</label>
                        <input type="text" name="coordinates" class="form-control" id="coordinates" value="{{ $editDisplay->coordinates }}">
                    </div>
                    <div class="mb-3">
                        <label for="font" class="form-label">Font</label>
                        <select class="form-select" name="font" id="font">
                            <option value="">Select Font</option>
                            <option value="F1" {{$editDisplay->font == 'F1' ? 'selected' : '' }}>Font 1</option>
                            <option value="F2" {{$editDisplay->font == 'F2' ? 'selected' : '' }}>Font 2</option>
                            <option value="F3" {{$editDisplay->font == 'F3' ? 'selected' : '' }}>Font 3</option>
                            <option value="F4" {{$editDisplay->font == 'F4' ? 'selected' : '' }}>Font 4</option>
                            <option value="F5" {{$editDisplay->font == 'F5' ? 'selected' : '' }}>Font 5</option>
                            <option value="F6" {{$editDisplay->font == 'F6' ? 'selected' : '' }}>Font 6</option>
                            <option value="F7" {{$editDisplay->font == 'F7' ? 'selected' : '' }}>Font 7</option>
                            <option value="F8" {{$editDisplay->font == 'F8' ? 'selected' : '' }}>Font 8</option>
                            <option value="F9" {{$editDisplay->font == 'F9' ? 'selected' : '' }}>Font 9</option>
                            <option value="F10" {{$editDisplay->font == 'F10' ? 'selected' : '' }}>Font 10</option>
                            <option value="F11" {{$editDisplay->font == 'F11' ? 'selected' : '' }}>Font 11</option>
                            <option value="F12" {{$editDisplay->font == 'F12' ? 'selected' : '' }}>Font 12</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="font_size" class="form-label">Font Size</label>
                        <select class="form-select" name="font_size" id="font_size">
                            <option value="">Select Font Size</option>
                            <option value="S0" {{$editDisplay->font_size == 'S0' ? 'selected' : '' }}>Size 0</option>
                            <option value="S1" {{$editDisplay->font_size == 'S1' ? 'selected' : '' }}>Size 1</option>
                            <option value="S2" {{$editDisplay->font_size == 'S2' ? 'selected' : '' }}>Size 2</option>
                            <option value="S3" {{$editDisplay->font_size == 'S3' ? 'selected' : '' }}>Size 3</option>
                            <option value="S4" {{$editDisplay->font_size == 'S4' ? 'selected' : '' }}>Size 4</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="color" class="form-label">Color</label>
                        <input type="text" class="form-control" name="color" placeholder="(R,G,B)" value="{{$editDisplay->color}}" id="color">
                            
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