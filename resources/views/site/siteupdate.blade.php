@extends('header')
@section('content')
<style>
    .sitelogo {
        display: block;
        width: 130px;
        border: 1px solid black;
        border-radius: 10px;
        margin-top: 5px;
    }

    label {
        margin-bottom: 0 !important;
    }
</style>
<div class="container">
    <div class="row">
        <div class="col-md-6 m-auto">
            <h4 class="text-center">Site Update Form</h4>
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <form action="{{ route('site.update', $siteData->site_id) }}" method="post" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="site_name" class="form-label">Site Name</label>
                        <input type="text" name="site_name" class="form-control" id="site_name" value="{{ $siteData->site_name }}">
                        <input type="hidden" name="site_name_old" id="site_name_old" value="{{ $siteData->site_name }}">
                    </div>
                    <div class="mb-3" style="pointer-events: none;">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" id="username" value="{{ $siteData->site_username }}">
                    </div>
                    <!-- <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="text" name="password" class="form-control" id="password" value="">
                    </div> -->
                    <div class="mb-3">
                        <label for="city" class="form-label">City</label>
                        <input type="text" name="city" class="form-control" id="city" value="{{ $siteData->site_city }}">
                    </div>
                    <div class="mb-3">
                        <label for="state" class="form-label">State</label>
                        <input type="text" name="state" class="form-control" id="state" value="{{ $siteData->site_state }}">
                    </div>
                    <div class="mb-3">
                        <label for="country" class="form-label">Country</label>
                        <input type="text" name="country" class="form-control" id="country" value="{{ $siteData->site_country }}">
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" id="location" value="{{ $siteData->site_location }}">
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" name="status" id="status">
                            <option value="">Select Status</option>
                            <option value="active" {{ $siteData->site_status == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $siteData->site_status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="suspended" {{ $siteData->site_status == 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="typeofproduct" class="form-label">Type of Product</label>
                        <select class="form-select" name="typeofproduct" id="typeofproduct">
                            <option value="">Select Product Type</option>
                            <option value="sspi" {{ $siteData->site_type_of_product == 'sspi' ? 'selected' : '' }}>SSPI</option>
                            <option value="eecs" {{ $siteData->site_type_of_product == 'eecs' ? 'selected' : '' }}>EECS</option>
                            <option value="findmycar" {{ $siteData->site_type_of_product == 'findmycar' ? 'selected' : '' }}>Find My Car</option>
                            <option value="slot_reservation" {{ $siteData->site_type_of_product == 'slot_reservation' ? 'selected' : '' }}>Slot Reservation</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="floors" class="form-label">Number of Floors</label>
                        <input type="text" name="floors" class="form-control" id="floors" value="{{ $siteData->number_of_floors }}">
                    </div>
                    <div class="mb-3">
                        <label for="zonals" class="form-label">Number of Zonals</label>
                        <input type="text" name="zonals" class="form-control" id="zonals" value="{{ $siteData->number_of_zonals }}">
                    </div>
                    <div class="mb-3">
                        <label for="sensors" class="form-label">Number of Sensors</label>
                        <input type="text" name="sensors" class="form-control" id="sensors" value="{{ $siteData->number_of_sensors }}">
                    </div>
                    <div class="mb-3">
                        <label for="displays" class="form-label">Number of Displays</label>
                        <input type="text" name="displays" class="form-control" id="displays" value="{{ $siteData->number_of_displays }}">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email (add multiple emails with comma seperate without space)</label>
                        <input type="email" name="email" class="form-control" id="email" value="{{ $siteData->email }}">
                    </div>
                    <div class="mb-3">
                        <label for="report" class="form-label">Report Frequency</label>
                        <select class="form-select" name="report" id="report">
                            <option value="">Select Report Frequency</option>
                            <option value="none" {{ $siteData->report_frequency == 'none' ? 'selected' : '' }}>None</option>
                            <option value="daily" {{ $siteData->report_frequency == 'daily' ? 'selected' : '' }}>Daily</option>
                            <option value="weekly" {{ $siteData->report_frequency == 'weekly' ? 'selected' : '' }}>Weekly</option>
                            <option value="monthly" {{ $siteData->report_frequency == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="financial_model" class="form-label">Financial Model</label>
                        <textarea name="financial_model" class="form-control textarea_height" placeholder='[{"minutes":amount}]' id="financial_model">{{ $siteData->financial_model }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="overtime_hours" class="form-label">Overtime Hours</label>
                        <input type="text" name="overtime_hours" class="form-control" placeholder='Hourse' value="{{ $siteData->overtime_hours }}" id="overtime_hours">
                    </div>
                    <div class="mb-3">
                        <label for="logo" class="form-label">Logo ( Max 2MB )</label>
                        <input type="file" name="logo" class="form-control" id="logo" accept="image/*">
                        <input type="hidden" name="old_logo" class="form-control" id="old_logo" value="{{ $siteData->site_logo }}">
                        <img id="uploadedImage" class="sitelogo" src="{{ asset('logos/'.$siteData->site_logo) }}" alt="Uploaded Image" accept="image/png, image/jpeg">
                    </div>
                    <div class="mb-3">
                        <label for="ad_image" class="form-label">Advertisement Image ( Max 2MB )</label>
                        <input type="file" name="ad_image" class="form-control" id="ad_image" accept="image/*">
                        <input type="hidden" name="old_ad_image" class="form-control" id="old_ad_image" value="{{ $siteData->ad_image }}">
                        <img id="uploadedAdImage" class="sitelogo" src="{{ asset('logos/'.$siteData->ad_image) }}" alt="Ad Image">
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

    document.getElementById('logo').addEventListener('change', function() {
        if (this.files[0]) {
            var picture = new FileReader();
            picture.readAsDataURL(this.files[0]);
            picture.addEventListener('load', function(event) {
                document.getElementById('uploadedImage').setAttribute('src', event.target.result);
                document.getElementById('uploadedImage').style.display = 'block';
            });
        } else {
            document.getElementById('uploadedImage').style.display = 'none';
        }
    });

    document.getElementById('ad_image').addEventListener('change', function() {
        if (this.files[0]) {
            var picture = new FileReader();
            picture.readAsDataURL(this.files[0]);
            picture.addEventListener('load', function(event) {
                document.getElementById('uploadedAdImage').setAttribute('src', event.target.result);
                document.getElementById('uploadedAdImage').style.display = 'block';
            });
        } else {
            document.getElementById('uploadedAdImage').style.display = 'none';
        }
    });
</script>
@endsection