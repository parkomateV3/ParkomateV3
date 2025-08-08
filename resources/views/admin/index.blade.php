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
    .dropdown-list,
    .dropdown-list-no,
    .dropdown-list-r {
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

    .capital::first-letter {
        text-transform: uppercase;
    }

    label {
        margin-bottom: 0 !important;
    }
</style>

<div class="container">
    <h2 class="text-center">Users Data</h2>
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
    <button class="btn btn-primary float-right" data-bs-toggle="modal" data-bs-target="#adminModal">Add Data</button>
    <br><br>
    <div class="row m-auto">
        <div class="col-md-12 m-auto">

            <table id="userTable" class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Name</th>
                        <th scope="col">Username</th>
                        <th scope="col">Role</th>
                        <th scope="col">Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <th scope="row">{{ $user->id }}</th>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td class="capital">{{ getRoleName($user->role_id) }}</td>
                        <td>
                            @if($user->status == 1)
                            <span class="badge badge-success">Active</span>
                            @else
                            <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                        <td>

                            <a href="admins/edit/{{$user->id}}" class="btn btn-sm btn-outline-success ">Edit</a>
                            <a href="admins/change/{{$user->id}}" class="btn btn-sm btn-outline-danger ">Change Password</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="adminModal" tabindex="-1">
    <div class="modal-dialog">
        ` <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Add User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin-store') }}" method="post">
                @csrf
                <div class="modal-body">

                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" id="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Username</label>
                        <input type="text" name="email" class="form-control" id="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Select Role</label>
                        <select name="role" class="form-select" id="role" required>
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                            <option value="{{$role['id']}}">{{$role['role_name']}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3 site" style="display: none;">
                        <label for="site" class="form-label">Select Site</label>
                        <select name="site" class="form-select" id="site">
                            <option value="">Select Site</option>
                            @foreach($sites as $site)
                            <option value="{{$site['site_id']}}">{{$site['site_name']}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3 multiselect-dropdown sites" style="display: none;">
                        <label for="logic_no" class="form-label">Select Sites</label>
                        <input type="text" placeholder="Select Sites..." class="search-box-no form-control" onclick="toggleDropdown1()" onkeyup="filterOptions1()">
                        <div class="dropdown-list-no" id="dropdown-list-no">
                            @foreach($sites as $site)
                            <label><input type="checkbox" class="check-no" value="{{$site['site_id']}}">{{$site['site_name']}}</label>
                            @endforeach

                        </div>
                        <input type="hidden" name="sites" id="sites">

                    </div>
                    <div class="mb-2 multiselect-dropdown slots" style="display: none;">
                        <input type="text" placeholder="Search..." class="search-box-r form-control" onclick="toggleDropdown2()" onkeyup="filterOptions2()">
                        <div class="dropdown-list-r" id="dropdown-list-r">

                        </div>

                    </div>
                    <div class="mb-3 slots" style="display: none;">
                        <textarea id="selectedValues" class="form-control" name="valueNames" id="valueNames" rows="3" readonly placeholder="Selected will appear here..."></textarea>
                        <input type="hidden" name="values" id="selectedval">
                    </div>
                    <div class="mb-2 multiselect-dropdown access" style="display: none;">
                        <label for="access" class="form-label">Page Access</label>
                        <input type="text" placeholder="Search..." class="search-box form-control" onclick="toggleDropdown()" onkeyup="filterOptions()">
                        <div class="dropdown-list" id="dropdown-list">
                            <label><input type="checkbox" class="check" value="table-view">Table View</label>
                            <label><input type="checkbox" class="check" value="detailed-view">Detailed View</label>
                            <label><input type="checkbox" class="check" value="floor-map">Floor Map</label>
                            <label><input type="checkbox" class="check" value="summary-report">Summary Report</label>
                            <label><input type="checkbox" class="check" value="historical-data">Historical Data</label>
                            <label><input type="checkbox" class="check" value="financial-model">Financial Model</label>
                        </div>

                    </div>
                    <div class="mb-3 access" style="display: none;">
                        <textarea id="selectedValuesNo" class="form-control" name="access" id="access" rows="3" readonly placeholder="Selected will appear here..."></textarea>
                    </div>
                    <div class="mb-3 canedit" style="display: none;">
                        <label for="canedit" class="form-label">Can Edit</label><br>
                        <select name="canedit" class="form-select" id="canedit">
                            <option value="">Select</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" id="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required />
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" class="form-select" id="status">
                            <option value="1">Active</option>
                            <option value="0" selected>Inactive</option>
                        </select>
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

    $(document).on('change', '#site', function() {
        var selectedValue = $(this).val();

        if (selectedValue) {
            $.ajax({
                url: '{{ url("check_reservation_site") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    site_id: selectedValue
                },
                success: function(response) {
                    if (response.data == 'slot_reservation') {
                        $('.slots').show();

                        $.ajax({
                            url: '{{ route("get_slots_data") }}',
                            type: 'post',
                            data: {
                                _token: '{{ csrf_token() }}',
                                site_id: selectedValue
                            },
                            success: function(response) {
                                // console.log(response);

                                // iterate floor groups
                                $('.dropdown-list-r').empty();
                                $.each(response, function(floorName, devices) {
                                    // console.log("Floor:", floorName);
                                    $('.dropdown-list-r').append('<label><b>' + floorName + '</b></label>');

                                    // each floor’s list of device objects
                                    $.each(devices, function(index, device) {
                                        // console.log(device);
                                        $('.dropdown-list-r').append('<label><input type="checkbox" data-id="' + device.id + '" class="check-r" id="floor" value="' + device.reservation_name + '">' + device.reservation_name + '</label>');

                                    });
                                });

                            },
                            error: function(xhr, status, error) {
                                console.log(xhr.responseText);
                            }
                        });
                    } else {
                        $('.slots').hide();
                    }

                },
                error: function(xhr, status, error) {
                    console.log(xhr.responseText);
                }
            });
        } else {
            alert("Please select a site.");
        }
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
    });

    $(document).on('change', '#role', function() {
        var id = $(this).val();
        // alert(id);
        if (id == 1) {
            $('.sites').css('display', 'none');
            $('.site').css('display', 'none');
            $('.canedit').css('display', 'none');
            $('#site').attr('required', false);
            $('#canedit').attr('required', false);
            $('.access').css('display', 'none');
        }
        if (id == 2) {
            $('.sites').css('display', 'block');
            $('.site').css('display', 'none');
            $('.canedit').css('display', 'block');
            $('#site').attr('required', false);
            $('#canedit').attr('required', true);
            $('.access').css('display', 'none');
        }
        if (id == 3) {
            $('.site').css('display', 'block');
            $('#site').attr('required', true);
            $('.sites').css('display', 'none');
            $('.canedit').css('display', 'none');
            $('.access').css('display', 'block');
        }
    });
</script>

@endsection