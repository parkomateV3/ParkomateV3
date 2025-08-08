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
            <h4 class="text-center">EECS Sensor Update</h4>
            <form action="{{ route('eecssensor.update', $editSensor->id) }}" method="post">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3" style="pointer-events: none;">
                        <label for="site_name" class="form-label">Site Name</label>
                        <input type="text" name="site_name" class="form-control" id="site_name" value="{{ getSitename($editSensor->site_id) }}">
                        <input type="hidden" name="site_id" class="form-control" id="site_id" value="{{ $editSensor->site_id }}">
                    </div>
                    <div class="mb-3" style="pointer-events: none;">
                        <label for="sensor_number" class="form-label">Sensor Number</label>
                        <input type="text" name="sensor_number" class="form-control" id="sensor_number" value="{{ $editSensor->sensor_number }}">
                    </div>
                    <div class="mb-3">
                        <label for="sensor_name" class="form-label">Sensor Name</label>
                        <input type="text" name="sensor_name" class="form-control" id="sensor_name" value="{{ $editSensor->sensor_name }}">
                    </div>
                    <div class="mb-3">
                        <label for="detection_type" class="form-label">Detection Type</label>
                        <select class="form-select" name="detection_type" id="detection_type" required>
                            <option value="" selected>Select Type</option>
                            @foreach($types as $type)
                            @if($type->id == $editSensor->detection_type)
                            <option value="{{ $type->id }}" selected>{{ $type->type }}</option>
                            @else
                            <option value="{{ $type->id }}">{{ $type->type }}</option>
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