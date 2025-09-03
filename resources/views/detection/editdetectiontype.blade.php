@extends('header')
@section('content')
<style>
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
            <h4 class="text-center">Edit Detection Data</h4>
            <form action="{{ route('updatedetectiontype') }}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="mb-3" style="pointer-events: none;">
                        <label for="site_name" class="form-label">Site Name</label>
                        <input type="text" name="site_name" class="form-control" id="site_name" value="{{ getSitename($detectionData->site_id) }}">
                        <input type="hidden" name="detection_id" value="{{ $detectionData->id }}">
                    </div>

                    <div class="mb-3" style="pointer-events: none;">
                        <label for="detection_type" class="form-label">Detection Type</label>
                        <input type="text" name="detection_type" class="form-control" id="detection_type" value="{{ getTypeName($detectionData->type_id) }}">
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">For Frontend Name</label>
                        <input type="text" name="name" class="form-control" id="name" value="{{ $detectionData->name }}">
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