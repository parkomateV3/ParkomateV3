@extends('header')
@section('content')
<style>
    .imagecss {
        display: block;
        width: 130px;
        border: 1px solid black;
        border-radius: 10px;
        margin-top: 5px;
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
            <h4 class="text-center">Floor Update</h4>
            <form action="{{ route('floor.update', $editFloor->floor_id) }}" method="post" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="site_id" class="form-label">Sites</label>
                        <select class="form-select" name="site_id" id="site_id" style="pointer-events: none;">
                            <option value="">Select Site</option>
                            @foreach($siteData as $site)
                            <option value="{{$site->site_id}}" {{ $site->site_id == $editFloor->site_id ? 'selected' : '' }}>{{$site->site_username}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="floor_name" class="form-label">Floor Name</label>
                        <input type="text" name="floor_name" class="form-control" id="floor_name" value="{{ $editFloor->floor_name }}" required>
                        <input type="hidden" name="floor_name_old" id="floor_name_old" value="{{ $editFloor->floor_name }}">
                    </div>
                    <div class="mb-3">
                        <label for="floor_image" class="form-label">Floor 3D Map Image ( Max 2MB )</label>
                        <input type="file" name="floor_image" class="form-control" id="floor_image" accept="image/*">
                        <input type="hidden" name="old_floor_image" class="form-control" id="old_floor_image" value="{{ $editFloor->floor_image }}">
                        <img id="uploadedFloorImage" class="imagecss" src="{{ asset('floors/'.$editFloor->floor_image ) }}" alt="Uploaded Image" accept="image/png, image/jpeg">
                    </div>
                    <div class="mb-3">
                        <label for="piller_name" class="form-label">Piller Name</label>
                        <textarea name="piller_name" class="form-control textarea_height" id="piller_name">{{ $editFloor->piller_name }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="piller_coordinates" class="form-label">Piller Coordinates</label>
                        <textarea name="piller_coordinates" class="form-control textarea_height" id="piller_coordinates">{{ $editFloor->piller_coordinates }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label for="current_location_symbol" class="form-label">Current Location Symbol ( Max 2MB )</label>
                        <input type="file" name="current_location_symbol" class="form-control" id="current_location_symbol" accept="image/*">
                        <input type="hidden" name="old_current_location_symbol" class="form-control" id="old_current_location_symbol" value="{{ $editFloor->current_location_symbol }}">
                        <img id="uploadedCurrentImage" class="imagecss" src="{{ asset('symbols/'.$editFloor->current_location_symbol ) }}" alt="Uploaded Image" accept="image/png, image/jpeg">
                    </div>
                    <div class="mb-3">
                        <label for="destination_location_symbol" class="form-label">Destination Location Symbol ( Max 2MB )</label>
                        <input type="file" name="destination_location_symbol" class="form-control" id="destination_location_symbol" accept="image/*">
                        <input type="hidden" name="old_destination_location_symbol" class="form-control" id="old_destination_location_symbol" value="{{ $editFloor->destination_location_symbol }}">
                        <img id="uploadedDestinationImage" class="imagecss" src="{{ asset('symbols/'.$editFloor->destination_location_symbol ) }}" alt="Uploaded Image" accept="image/png, image/jpeg">
                    </div>
                    <div class="mb-3">
                        <label for="interconnect_location_symbol" class="form-label">Interconnect Location Symbol ( Max 2MB )</label>
                        <input type="file" name="interconnect_location_symbol" class="form-control" id="interconnect_location_symbol" accept="image/*">
                        <input type="hidden" name="old_interconnect_location_symbol" class="form-control" id="old_interconnect_location_symbol" value="{{ $editFloor->interconnect_location_symbol }}">
                        <img id="uploadedInterconnectImage" class="imagecss" src="{{ asset('symbols/'.$editFloor->interconnect_location_symbol ) }}" alt="Uploaded Image" accept="image/png, image/jpeg">
                    </div>
                    <div class="mb-3">
                        <label for="symbol_size" class="form-label">Symbol Size</label>
                        <input type="text" name="symbol_size" class="form-control" placeholder="height,width,radius,spacing,max_distance" id="symbol_size" value="{{ $editFloor->symbol_size }}">
                    </div>
                    <div class="mb-3">
                        <label for="floor_image_sensor_mapping_dimenssion" class="form-label">Floor Mapping Dimensions</label>
                        <input type="text" name="floor_image_sensor_mapping_dimenssion" class="form-control" id="floor_image_sensor_mapping_dimenssion" placeholder="width, height" value="{{ $editFloor->floor_image_sensor_mapping_dimenssion }}">
                    </div>
                    <div class="mb-3">
                        <label for="car_scale" class="form-label">Floor Car Scale</label>
                        <input type="text" name="car_scale" class="form-control" id="car_scale" placeholder="x, y, z" value="{{ $editFloor->car_scale }}">
                    </div>
                    <div class="mb-3">
                        <label for="label_properties" class="form-label">Label Size</label>
                        <input type="text" name="label_properties" class="form-control" id="label_properties" placeholder="label size" value="{{ $editFloor->label_properties }}">
                    </div>
                    <div class="mb-3">
                        <label for="floor_map_coordinate" class="form-label">Floor Map Coordinates</label>
                        <textarea name="floor_map_coordinate" class="form-control textarea_height" id="floor_map_coordinate" placeholder='[{"x":"250","y":"85","z":"0","a":"180","label":"S1-TEST","i_color":"r","v_color":"r","sensor_id":"1"}]'>{{ $editFloor->floor_map_coordinate }}</textarea>
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

    document.getElementById('floor_image').addEventListener('change', function() {
        if (this.files[0]) {
            var picture = new FileReader();
            picture.readAsDataURL(this.files[0]);
            picture.addEventListener('load', function(event) {
                document.getElementById('uploadedFloorImage').setAttribute('src', event.target.result);
                document.getElementById('uploadedFloorImage').style.display = 'block';
            });
        } else {
            document.getElementById('uploadedFloorImage').style.display = 'none';
        }
    });

    document.getElementById('current_location_symbol').addEventListener('change', function() {
        if (this.files[0]) {
            var picture = new FileReader();
            picture.readAsDataURL(this.files[0]);
            picture.addEventListener('load', function(event) {
                document.getElementById('uploadedCurrentImage').setAttribute('src', event.target.result);
                document.getElementById('uploadedCurrentImage').style.display = 'block';
            });
        } else {
            document.getElementById('uploadedCurrentImage').style.display = 'none';
        }
    });

    document.getElementById('destination_location_symbol').addEventListener('change', function() {
        if (this.files[0]) {
            var picture = new FileReader();
            picture.readAsDataURL(this.files[0]);
            picture.addEventListener('load', function(event) {
                document.getElementById('uploadedDestinationImage').setAttribute('src', event.target.result);
                document.getElementById('uploadedDestinationImage').style.display = 'block';
            });
        } else {
            document.getElementById('uploadedDestinationImage').style.display = 'none';
        }
    });

    document.getElementById('interconnect_location_symbol').addEventListener('change', function() {
        if (this.files[0]) {
            var picture = new FileReader();
            picture.readAsDataURL(this.files[0]);
            picture.addEventListener('load', function(event) {
                document.getElementById('uploadedInterconnectImage').setAttribute('src', event.target.result);
                document.getElementById('uploadedInterconnectImage').style.display = 'block';
            });
        } else {
            document.getElementById('uploadedInterconnectImage').style.display = 'none';
        }
    });
</script>
@endsection