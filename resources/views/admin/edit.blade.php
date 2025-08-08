@extends('header')
@section('content')

<style>
    .check {
        margin: 0px 10px;
    }

    /* Style for the dropdown */
    .multiselect-dropdown {
        width: 100%;
        position: relative;
    }

    /* Search box inside the dropdown */
    .search-box,
    .search-box-no,
    .search-box-r {
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
        z-index: 99;
    }

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

    .dropdown-list-r {
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

    .dropdown-list-r label {
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

    .dropdown-list-r label:hover {
        background-color: #f0f0f0;
    }

    .dropdown-list input {
        margin-right: 5px;
    }

    .dropdown-list-no input {
        margin-right: 5px;
    }

    .dropdown-list-r input {
        margin-right: 5px;
    }

    .dropdown-list.active {
        display: block;
    }

    .dropdown-list-no.active {
        display: block;
    }

    .dropdown-list-r.active {
        display: block;
    }

    label {
        margin-bottom: 0 !important;
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
            <h4 class="text-center">User Data Update {{ $user->slots_ids }}</h4>
            <form action="{{ route('admins/edit') }}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="mb-3" style="pointer-events: none;">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" id="name" value="{{ $user->name }}" required>
                    </div>
                    <div class="mb-3" style="pointer-events: none;">
                        <label for="email" class="form-label">Username</label>
                        <input type="text" name="email" class="form-control" id="email" value="{{ $user->email }}" required>
                    </div>
                    <div class="mb-3" style="pointer-events: none;">
                        <label for="role" class="form-label">Role</label>
                        <input type="text" class="form-control" value="{{ getRoleName($user->role_id) }}">
                        <input type="hidden" name="role" class="form-control" id="role" value="{{ $user->role_id }}">
                        <input type="hidden" name="id" class="form-control" id="id" value="{{ $user->id }}">
                    </div>
                    @if($user->role_id == 2)
                    <div class="mb-3 multiselect-dropdown sites">
                        <label for="logic_no" class="form-label">Select Sites</label>
                        <input type="text" placeholder="Select Sites..." class="search-box-no form-control" onclick="toggleDropdown1()" onkeyup="filterOptions1()">
                        <div class="dropdown-list-no" id="dropdown-list-no">
                            @foreach($sites as $site)
                            @if(in_array($site->site_id, $siteIds))
                            <label><input type="checkbox" class="check-no" value="{{$site['site_id']}}" checked>{{$site['site_name']}}</label>
                            @else
                            <label><input type="checkbox" class="check-no" value="{{$site['site_id']}}">{{$site['site_name']}}</label>
                            @endif
                            @endforeach

                        </div>
                        <input type="hidden" name="sites" id="sites" value="{{$siteids}}">

                    </div>
                    @endif
                    @if($user->role_id == 3)
                    <div class="mb-3 site">
                        <label for="site" class="form-label">Select Site</label>
                        <select name="site" class="form-select" id="site">
                            <option value="">Select Site</option>
                            @foreach($sites as $site)
                            @if($site->site_id == $user->site_id)
                            <option value="{{$site['site_id']}}" selected>{{$site['site_name']}}</option>
                            @else
                            <option value="{{$site['site_id']}}">{{$site['site_name']}}</option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2 multiselect-dropdown slots" style="display: none;">
                        <input type="text" placeholder="Search..." class="search-box-r form-control" onclick="toggleDropdown2()" onkeyup="filterOptions2()">
                        <div class="dropdown-list-r" id="dropdown-list-r">

                        </div>

                    </div>
                    <div class="mb-3 slots" style="display: none;">
                        <textarea id="selectedValues" class="form-control" name="valueNames" id="valueNames" rows="3" readonly placeholder="Selected will appear here...">{{ $user->slots_names }}</textarea>
                        <input type="hidden" name="values" id="selectedval" value="{{ $user->slots_ids }}">
                    </div>
                    <div class="mb-2 multiselect-dropdown access">
                        <label for="access" class="form-label">Page Access</label>
                        <input type="text" placeholder="Search..." class="search-box form-control" onclick="toggleDropdown()" onkeyup="filterOptions()">
                        <div class="dropdown-list" id="dropdown-list">
                            <label><input type="checkbox" class="check" {{ in_array('table-view', $access) ? 'checked' : '' }} value="table-view">Table View</label>
                            <label><input type="checkbox" class="check" {{ in_array('detailed-view', $access) ? 'checked' : '' }} value="detailed-view">Detailed View</label>
                            <label><input type="checkbox" class="check" {{ in_array('floor-map', $access) ? 'checked' : '' }} value="floor-map">Floor Map</label>
                            <label><input type="checkbox" class="check" {{ in_array('summary-report', $access) ? 'checked' : '' }} value="summary-report">Summary Report</label>
                            <label><input type="checkbox" class="check" {{ in_array('historical-data', $access) ? 'checked' : '' }} value="historical-data">Historical Data</label>
                            <label><input type="checkbox" class="check" {{ in_array('financial-model', $access) ? 'checked' : '' }} value="financial-model">Financial Model</label>
                        </div>

                    </div>
                    <div class="mb-3 access">
                        <textarea id="selectedValuesNo" class="form-control" name="access" id="access" rows="3" readonly placeholder="Selected will appear here...">{{$user->access}}</textarea>
                    </div>
                    @endif
                    @if($user->role_id == 2)
                    <div class="mb-3">
                        <label for="canedit" class="form-label">Can Edit</label><br>
                        <select name="canedit" class="form-select" id="canedit">
                            <option value="1" {{$user->can_edit == 1 ? 'selected' : ''}}>Yes</option>
                            <option value="0" {{$user->can_edit == 0 ? 'selected' : ''}}>No</option>
                        </select>

                    </div>
                    @endif
                    <div class="mb-3">
                        <label for="status" class="form-label">Select Status</label>
                        <select name="status" class="form-select" id="status">
                            <option value="1" {{ $user->status == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ $user->status == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
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
@php
$slots = explode(',', $user->slots_ids);
@endphp
<div id="data-container"
    data-ids='@json($slots)'>
</div>
<script>
    // Toggle the display of the dropdown list
    function toggleDropdown() {
        document.getElementById("dropdown-list").classList.toggle("active");
    }

    function toggleDropdown1() {
        document.getElementById("dropdown-list-no").classList.toggle("active");
    }

    function toggleDropdown2() {
        document.getElementById("dropdown-list-r").classList.toggle("active");
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

    function filterOptions2() {
        const searchBox = document.querySelector('.search-box-r').value.toLowerCase();
        const labels = document.querySelectorAll('.dropdown-list-r label');

        labels.forEach(label => {
            const text = label.innerText.toLowerCase();
            if (text.includes(searchBox)) {
                label.style.display = "block";
            } else {
                label.style.display = "none";
            }
        });
    }

    $(document).ready(function() {
        var container = document.getElementById('data-container');
        var ids = JSON.parse(container.dataset.ids);
        // console.log(ids);

        // Define the logic as a reusable function
        function loadSlotsForSite() {
            const selectedValue = $('#site').val();

            if (!selectedValue) {
                alert("Please select a site.");
                return;
            }

            // First AJAX call
            $.ajax({
                url: '{{ url("check_reservation_site") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    site_id: selectedValue
                },
                success: function(response) {
                    if (response.data === 'slot_reservation') {
                        $('.slots').show();
                        // Second AJAX call to load slots
                        $.ajax({
                            url: '{{ route("get_slots_data") }}',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                site_id: selectedValue
                            },
                            success: function(response) {
                                $('.dropdown-list-r').empty();
                                $.each(response, function(floorName, devices) {
                                    $('.dropdown-list-r').append('<label><b>' + floorName + '</b></label>');
                                    $.each(devices, function(_, device) {
                                        // Check if the device ID is in the ids array
                                        if (ids.includes(device.id.toString())) {
                                            $('.dropdown-list-r').append(
                                                '<label><input type="checkbox" data-id="' + device.id +
                                                '" class="check-r" value="' + device.reservation_name +
                                                '" checked> ' + device.reservation_name + '</label>'
                                            );
                                        } else {
                                            $('.dropdown-list-r').append(
                                                '<label><input type="checkbox" data-id="' + device.id +
                                                '" class="check-r" value="' + device.reservation_name +
                                                '"> ' + device.reservation_name + '</label>'
                                            );
                                        }
                                    });
                                });
                            },
                            error: function(xhr) {
                                console.log(xhr.responseText);
                            }
                        });
                    } else {
                        $('.slots').hide();
                    }
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                }
            });
        }

        // Run on page load
        loadSlotsForSite();

        // Also run on change of #site
        $(document).on('change', '#site', loadSlotsForSite);
    });

    $(document).on('change', '.dropdown-list-r input[type="checkbox"]', function() {
        // Collect checked inputs
        const checked = $('.dropdown-list-r input[type="checkbox"]:checked');

        // Get array of reservation_name values
        const names = checked.map((_, el) => el.value).get();

        // Get array of device IDs (using data-id attribute or class)
        const ids = checked.map((_, el) => $(el).data('id') || el.classList[1]).get();

        // Set textarea to comma-separated names
        $('#selectedValues').val(names.join(','));

        // Store IDs in hidden input (comma-separated)
        $('#selectedval').val(ids.join(','));
    });

    $(document).on('change', '.check', function() {
        const checkboxes = document.querySelectorAll('#dropdown-list label input[type="checkbox"]');
        let selected = [];


        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                selected.push(checkbox.value);
            }
        });

        document.getElementById("selectedValuesNo").value = selected.join(',');
    });

    $(document).on('change', '.check-no', function() {
        const checkboxes = document.querySelectorAll('#dropdown-list-no label input[type="checkbox"]');
        let selected = [];


        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                selected.push(checkbox.value);
            }
        });

        document.getElementById("sites").value = selected.join(',');
    })

    function goBack() {
        window.history.back(); // or use window.history.go(-1);
    }
</script>
@endsection