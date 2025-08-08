@extends('header')
@section('content')
<style>
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
            <h4 class="text-center">Sensor Update</h4>
            <form action="{{ route('sensor.update', $editSensor->sensor_id) }}" method="post">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3" style="pointer-events: none;">
                        <label for="site_name" class="form-label">Site Name</label>
                        <input type="text" name="site_name" class="form-control" id="site_name" value="{{ getSitename($editSensor->site_id) }}">
                        <input type="hidden" name="site_id" value="{{ $editSensor->site_id }}">
                    </div>
                    <div class="mb-3" style="pointer-events: none;">
                        <label for="floor_name" class="form-label">Floor Name</label>
                        <input type="text" name="floor_name" class="form-control" id="floor_name" value="{{ getFloorname($editSensor->floor_id) }}">
                        <input type="hidden" name="floor_id" value="{{ $editSensor->floor_id }}">
                    </div>
                    <div class="mb-3" style="pointer-events: none;">
                        <label for="zonal_name" class="form-label">Zonal Name</label>
                        <input type="text" name="zonal_name" class="form-control" id="zonal_name" value="{{ getZonalname($editSensor->zonal_id) }}">
                        <input type="hidden" name="zonal_id" value="{{ $editSensor->zonal_id }}">
                    </div>
                    <div class="mb-3">
                        <label for="sensor_name" class="form-label">Sensor Name</label>
                        <input type="text" name="sensor_name" class="form-control" id="sensor_name" value="{{ $editSensor->sensor_name }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="sensor_no" class="form-label">Sensor Number</label>
                        <input type="text" name="sensor_no" class="form-control" id="sensor_no" value="{{ $editSensor->sensor_unique_no }}" required>
                        <input type="hidden" name="sensor_no_old" value="{{ $editSensor->sensor_unique_no }}">
                    </div>
                    <div class="mb-3">
                        <label for="near_piller" class="form-label">Select near piller</label>
                        <select class="form-select" name="near_piller" id="near_piller">
                            <option value="">Select Piller</option>
                            @foreach($pillerData as $piller)
                            @if($editSensor->near_piller == $piller)
                            <option value="{{$piller}}" selected>{{$piller}}</option>
                            @else
                            <option value="{{$piller}}">{{$piller}}</option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="sensor_range" class="form-label">Sensor Range</label>
                        <input type="text" name="sensor_range" class="form-control" id="sensor_range" value="{{ $editSensor->sensor_range }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="color_occupied" class="form-label">Color Occupied</label>
                        <select class="form-select" name="color_occupied" id="color_occupied" required>
                            <option value="">Select Color</option>
                            <option value="red" {{$editSensor->color_occupied == 'red' ? 'selected' : '' }}>Red</option>
                            <option value="green" {{$editSensor->color_occupied == 'green' ? 'selected' : '' }}>Green</option>
                            <option value="blue" {{$editSensor->color_occupied == 'blue' ? 'selected' : '' }}>Blue</option>
                            <option value="magenta" {{$editSensor->color_occupied == 'magenta' ? 'selected' : '' }}>Magenta</option>
                            <option value="yellow" {{$editSensor->color_occupied == 'yellow' ? 'selected' : '' }}>Yellow</option>
                            <option value="cyan" {{$editSensor->color_occupied == 'cyan' ? 'selected' : '' }}>Cyan</option>
                            <option value="white" {{$editSensor->color_occupied == 'white' ? 'selected' : '' }}>White</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="color_available" class="form-label">Color Available</label>
                        <select class="form-select" name="color_available" id="color_available" required>
                            <option value="">Select Color</option>
                            <option value="red" {{$editSensor->color_available == 'red' ? 'selected' : '' }}>Red</option>
                            <option value="green" {{$editSensor->color_available == 'green' ? 'selected' : '' }}>Green</option>
                            <option value="blue" {{$editSensor->color_available == 'blue' ? 'selected' : '' }}>Blue</option>
                            <option value="magenta" {{$editSensor->color_available == 'magenta' ? 'selected' : '' }}>Magenta</option>
                            <option value="yellow" {{$editSensor->color_available == 'yellow' ? 'selected' : '' }}>Yellow</option>
                            <option value="cyan" {{$editSensor->color_available == 'cyan' ? 'selected' : '' }}>Cyan</option>
                            <option value="white" {{$editSensor->color_available == 'white' ? 'selected' : '' }}>White</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" name="role" id="role" required>
                            <option value="">Select Role</option>
                            <option value="loop" {{$editSensor->role == 'loop' ? 'selected' : '' }}>Loop</option>
                            <option value="single" {{$editSensor->role == 'single' ? 'selected' : '' }}>Single</option>
                            <option value="off" {{$editSensor->role == 'off' ? 'selected' : '' }}>Off</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="barrier_id" class="form-label">Barrier ID</label>
                        <input type="text" name="barrier_id" class="form-control" id="barrier_id" value="{{$editSensor->barrier_id}}">
                    </div>
                    <div class="mb-3">
                        <label for="barrier_color" class="form-label">Barrier Color</label>
                        <select class="form-select" name="barrier_color" id="barrier_color">
                            <option value="">Select Barrier Color</option>
                            <option value="red" {{$editSensor->barrier_color == 'red' ? 'selected' : '' }}>Red</option>
                            <option value="green" {{$editSensor->barrier_color == 'green' ? 'selected' : '' }}>Green</option>
                            <option value="blue" {{$editSensor->barrier_color == 'blue' ? 'selected' : '' }}>Blue</option>
                            <option value="magenta" {{$editSensor->barrier_color == 'magenta' ? 'selected' : '' }}>Magenta</option>
                            <option value="yellow" {{$editSensor->barrier_color == 'yellow' ? 'selected' : '' }}>Yellow</option>
                            <option value="cyan" {{$editSensor->barrier_color == 'cyan' ? 'selected' : '' }}>Cyan</option>
                            <option value="white" {{$editSensor->barrier_color == 'white' ? 'selected' : '' }}>White</option>
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

<script>
    function goBack() {
        window.history.back(); // or use window.history.go(-1);
    }
</script>

@endsection