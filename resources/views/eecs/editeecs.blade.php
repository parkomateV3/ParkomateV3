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
            <h4 class="text-center">EECS Data Update</h4>
            <form action="{{ route('eecs.update', $editSensor->id) }}" method="post">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3" style="pointer-events: none;">
                        <label for="site_name" class="form-label">Site Name</label>
                        <input type="text" name="site_name" class="form-control" id="site_name" value="{{ getSitename($editSensor->site_id) }}">
                        <input type="hidden" name="site_id" class="form-control" id="site_id" value="{{ $editSensor->site_id }}">
                    </div>
                    <div class="mb-3" style="pointer-events: none;">
                        <label for="device_name" class="form-label">Device Name</label>
                        <input type="text" name="device_name" class="form-control" id="device_name" value="{{ getDeviceName($editSensor->device_id) }}">
                        <input type="hidden" name="device_id" class="form-control" id="device_id" value="{{ $editSensor->device_id }}">
                    </div>
                    <div class="mb-3" style="pointer-events: none;">
                        <label for="sensor_name" class="form-label">Sensor Name</label>
                        <input type="text" name="sensor_name" class="form-control" id="sensor_name" value="{{ getEECSSensorNameNo($editSensor->sensor_id) }}">
                        <input type="hidden" name="sensor_id" class="form-control" id="sensor_id" value="{{ $editSensor->sensor_id }}">
                    </div>
                    <div class="mb-3">
                        <label for="from" class="form-label">From</label>
                        <select class="form-select" name="from" id="from" required>
                            <option value="">Select Floor</option>
                            <option value="0" {{$editSensor->from == 0 ? 'selected' : ''}}>Entry</option>
                            @foreach($floorData as $floor)
                            @if($floor->floor_id == $editSensor->from)
                            <option value="{{ $floor->floor_id }}" selected>{{ $floor->floor_name }}</option>
                            @else
                            <option value="{{ $floor->floor_id }}">{{ $floor->floor_name }}</option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="to" class="form-label">To</label>
                        <select class="form-select" name="to" id="to" required>
                            <option value="" selected>Select Floor</option>
                            <option value="0" {{$editSensor->to == 0 ? 'selected' : ''}}>Exit</option>
                            @foreach($floorData as $floor)
                            @if($floor->floor_id == $editSensor->to)
                            <option value="{{ $floor->floor_id }}" selected>{{ $floor->floor_name }}</option>
                            @else
                            <option value="{{ $floor->floor_id }}">{{ $floor->floor_name }}</option>
                            @endif
                            @endforeach
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